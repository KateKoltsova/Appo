<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\CartAddRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\User;
use App\Services\BlockService;
use App\Services\LiqpayService;
use App\Services\TotalSumService;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $user)
    {
        $cart = Cart::select([
            'carts.*',
            'services.title',
            'services.category',
            'masters.firstname as master_firstname',
            'masters.lastname as master_lastname',
            'schedules.date_time',
            'schedules.status',
            'schedules.blocked_until',
            'schedules.blocked_by',
            'prices.price'
        ])
            ->join('services', 'carts.service_id', '=', 'services.id')
            ->join('schedules', 'carts.schedule_id', '=', 'schedules.id')
            ->join('users as masters', 'schedules.master_id', '=', 'masters.id')
            ->join('prices', 'carts.price_id', '=', 'prices.id')
            ->where('carts.client_id', '=', $user)
            ->get();

        $cartCollection = CartResource::collection($cart);
        $totalSum = TotalSumService::totalSum($cartCollection);
        $response = ['data' => []];
        $response['data']['items'] = $cartCollection;
        $response['data']['totalSum'] = $totalSum['totalSum'];
        $response['data']['totalCount'] = $totalSum['totalCount'];
        return response()->json($response);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartAddRequest $request, string $user)
    {
        $params = $request->validated();
        $params['client_id'] = $user;

        $schedule = Schedule::findOrFail($params['schedule_id']);

        if ($schedule->status === config('constants.db.status.unavailable') ||
            (!is_null($schedule->blocked_by)
                && $schedule->blocked_by != $user
                && !is_null($schedule->blocked_until)
                && ($schedule->blocked_until >= now()->setTimezone('Europe/Kiev'))) ||
            ($schedule->date_time < now()->setTimezone('Europe/Kiev'))) {
            return response()->json(['message' => 'Schedule already unavailable'], 400);
        }

        $inCart = Cart::where('client_id', $user)->pluck('schedule_id')->toArray();

        $otherSchedules = Schedule::whereIn('id', $inCart)->get();

        foreach ($otherSchedules->toArray() as $otherSchedule) {

            if ($otherSchedule['status'] === config('constants.db.status.unavailable') ||
                (!is_null($otherSchedule['blocked_by'])
                    && $otherSchedule['blocked_by'] != $user
                    && !is_null($otherSchedule['blocked_until'])
                    && ($otherSchedule['blocked_until'] >= now()->setTimezone('Europe/Kiev'))) ||
                ($otherSchedule['date_time'] < now()->setTimezone('Europe/Kiev'))) {
                continue;
            }

            $timeInCart = new DateTime($otherSchedule['date_time']);
            $timeAdding = new DateTime($schedule->date_time);
            $diff = $timeInCart->diff($timeAdding);
            $diffMinutes = $diff->i + $diff->h * 60 + $diff->d * 24 * 60;

            if ($diffMinutes === 0) {
                return response()->json(['message' => 'You already have the same date-time in the cart'], 400);
            }

            if ($diffMinutes < config('constants.db.diff_between_services.minutes')) {
                return response()->json(['message' => 'Selected time too close to items in cart'], 400);
            }
        }

        if (!$schedule->master()->first()->prices()->whereId($params['price_id'])->first()) {
            return response()->json(['message' => 'This master does not provide such a service'], 400);
        }

        $cart = Cart::create($params);

        if ($cart) {
            return $this->index($user);
        } else {
            return response()->json(['message' => 'Bad request'], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $user, string $cart)
    {
        $cartItem = Cart::where('id', $cart)->where('client_id', $user)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $schedule = $cartItem->schedule()->first();
        if ($schedule->blocked_by == $user) {
            BlockService::unblock($schedule);
        }
        $cartItem->delete();

        return response()->json(['message' => 'Cart item successfully deleted']);
    }

    public function checkout(string $user)
    {
        try {
            $cart = $this->index($user)->original['data'];

            if (is_null($cart)) {
                throw new Exception('Cart is empty');
            }

            DB::beginTransaction();

            foreach ($cart['items'] as $cartItem) {
                $cartItemSchedule = Schedule::firstWhere('id', $cartItem['schedule_id']);

                if ($cartItemSchedule->status === config('constants.db.status.unavailable') ||
                    (!is_null($cartItemSchedule->blocked_by)
                        && $cartItemSchedule->blocked_by != $user
                        && !is_null($cartItemSchedule->blocked_until)
                        && ($cartItemSchedule->blocked_until >= now()->setTimezone('Europe/Kiev'))) ||
                    ($cartItemSchedule->date_time < now()->setTimezone('Europe/Kiev'))) {
                    throw new Exception('Some schedules in cart already unavailable');
                }

                BlockService::block(config('constants.db.blocked.minutes'), $user, $cartItemSchedule);
            }

            $total = TotalSumService::totalSum($cart['items'], 'payment');

            if (is_null($total)) {
                throw new Exception('Total sum error');
            }

            $cart['totalSum'] = $total['totalSum'];
            $cart['totalCount'] = $total['totalCount'];

            DB::commit();

            return response()->json(['data' => $cart]);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function getPayButton(OrderCreateRequest $request, string $user)
    {
        try {
            $params = $request->validated();
            $userId = User::findOrFail($user)->id;

            $carts = $this->index($userId)->original['data'];

            if (is_null($carts)) {
                throw new Exception('Cart is empty');
            }

            DB::beginTransaction();

            foreach ($carts['items'] as $cart) {
                $cartItemSchedule = Schedule::firstWhere('id', $cart['schedule_id']);

                if ($cartItemSchedule->status === config('constants.db.status.unavailable') ||
                    (!is_null($cartItemSchedule->blocked_by)
                        && $cartItemSchedule->blocked_by != $userId
                        && !is_null($cartItemSchedule->blocked_until)
                        && ($cartItemSchedule->blocked_until >= now()->setTimezone('Europe/Kiev'))) ||
                    ($cartItemSchedule->date_time < now()->setTimezone('Europe/Kiev'))) {
                    throw new Exception('Some schedules in cart already unavailable');
                }

                if (is_null($cartItemSchedule->blocked_by) ||
                    ($cartItemSchedule->blocked_by != $userId) ||
                    ($cartItemSchedule->blocked_until < now()->setTimezone('Europe/Kiev'))) {
                    throw new Exception('You must checkout first');
                }

                $expired_at = Carbon::createFromDate($cartItemSchedule->blocked_until, 'Europe/Kiev')->setTimezone('UTC');
            }

            $total = TotalSumService::totalSum($carts['items'], $params['payment']);

            if (is_null($total)) {
                throw new Exception('Total sum error');
            }

            $orderParams = [
                'user_id' => $userId,
                'total' => $total['totalSum'],
                'payment' => $params['payment']
            ];
            $order = Order::create($orderParams);

            $resultUrl = $params['result_url'];
            $paidParams['payment'] = $params['payment'];
            $paidParams['order_id'] = $order->id;
            $paidParams['html_button'] = LiqpayService::getHtml($total['totalSum'], $order->id, $expired_at, $resultUrl);

            DB::commit();

            return response()->json(['data' => $paidParams]);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 400);

        }
    }
}
