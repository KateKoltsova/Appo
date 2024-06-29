<?php

namespace App\Services\Api;

use App\Http\Resources\CartCollection;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Schedule;
use App\Repositories\CartRepository;
use App\Services\Contracts\BlockModel;
use App\Services\Contracts\PayService;
use App\Services\TotalService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        private BlockModel      $blockModel,
        private PayService      $payService,
        private CartRepository  $cartRepository,
        private ScheduleService $scheduleService,
    )
    {
    }

    /**
     * Get all cart items from DB and make Collection
     */
    public function getCartCollection(string $userId)
    {
        $cart = $this->cartRepository->getAllByUserId($userId);
        return new CartCollection($cart);
    }

    /**
     * Display a listing of the cart items.
     */
    public function getList(string $userId)
    {
        $cartCollection = $this->getCartCollection($userId);

        $total = TotalService::total($cartCollection->toArray(request()));

        $response['data']['items'] = $cartCollection;
        $response['data']['totalSum'] = $total['totalSum'];
        $response['data']['totalCount'] = $total['totalCount'];
        return $response;
    }

    /**
     * Add a new item to the cart.
     */
    public function add(array $params, string $userId)
    {
        try {
            $params['client_id'] = $userId;

            $addingSchedule = Schedule::findOrFail($params['schedule_id']);

            $schedulesChecked = $this->scheduleService->check($userId, $addingSchedule);

            if (!$schedulesChecked) {
                throw new Exception('Invalid schedule', 400);
            }

            DB::beginTransaction();

            $cart = Cart::create($params);

            if (!$cart) {
                throw new Exception('Bad request', 400);
            }

            $carts = $this->getList($userId);

            if ($carts['data']['totalCount'] > config('constants.db.cart_limit.items')) {
                throw new Exception('You can\'t add more cart items', 400);
            }

            DB::commit();

            return $carts;

        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Remove the specified item from cart.
     */
    public function delete(string $userId, string $cartId)
    {
        try {
            $cartItem = Cart::where('id', $cartId)->where('client_id', $userId)->first();

            if (!$cartItem) {
                throw new Exception('Cart item not found', 404);
            }

            $schedule = $cartItem->schedule()->first();

            if ($schedule->blocked_by == $userId) {
                $this->blockModel->unblock($schedule);
            }

            $cartItem->delete();

            return true;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get way to pay and blocked specified resource by user
     */
    public function checkout(string $userId)
    {
        try {
            $cart = $this->getCartCollection($userId);

            if ($cart->isEmpty()) {
                throw new Exception('Cart is empty', 400);
            }

            DB::beginTransaction();

            $schedulesChecked = $this->scheduleService->check($userId);

            if (!$schedulesChecked) {
                throw new Exception('You have invalid schedule in your cart', 400);
            }

            $schedulesChecked->each(function ($schedule) use ($userId) {
                $this->blockModel->block(config('constants.db.blocked.minutes'), $userId, $schedule);
            });

            $total = TotalService::total($cart->toArray(request()), 'payment');

            if (is_null($total)) {
                throw new Exception('Total sum error');
            }

            $response['data']['items'] = $cart;
            $response['data']['totalSum'] = $total['totalSum'];
            $response['data']['totalCount'] = $total['totalCount'];

            DB::commit();

            return $response;

        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Create order and get payment button with link and data for pay order
     */
    public function getPayButton($params, string $userId)
    {
        try {
            $cart = $this->getCartCollection($userId);

            if ($cart->isEmpty()) {
                throw new Exception('Cart is empty', 400);
            }

            DB::beginTransaction();

            $schedulesChecked = $this->scheduleService->check($userId);

            if (!$schedulesChecked) {
                throw new Exception('You have invalid schedule in your cart', 400);
            }

            $schedulesChecked->every(function ($schedule) use ($userId) {
                if (is_null($schedule->blocked_by) ||
                    ($schedule->blocked_by != $userId) ||
                    ($schedule->blocked_until < now()->setTimezone('Europe/Kiev'))) {
                    throw new Exception('You must checkout first', 400);
                }
            });

            $expired_at = Carbon::createFromDate($schedulesChecked->first()->blocked_until, 'Europe/Kiev')->setTimezone('UTC');;

            $total = TotalService::total($cart->toArray(request()), $params['payment']);

            if (is_null($total)) {
                throw new Exception('Total sum error', 400);
            }

            $orderParams = [
                'user_id' => $userId,
                'total' => $total['totalSum'],
                'payment' => $params['payment'],
            ];
            $order = Order::create($orderParams);

            $description = 'Pay for beauty services:';
            foreach ($schedulesChecked as $item) {
                $description .= ", \n" . $item->date_time . '-' . $item->user()->first_name . $item->user()->last_name;
            }

            $resultUrl = $params['result_url'];
            $paidParams['payment'] = $params['payment'];
            $paidParams['order_id'] = $order->id;
            $paidParams['html_button'] = $this->payService->getHtml($total['totalSum'], $order->id, $expired_at, $resultUrl, $description);

            DB::commit();

            return ['data' => $paidParams];

        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
