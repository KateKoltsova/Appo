<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\CartAddRequest;
use App\Http\Resources\CartCollection;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\User;
use App\Services\Contracts\BlockModel;
use App\Services\Contracts\PayService;
use App\Services\ScheduleService;
use App\Services\TotalService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function __construct(private BlockModel $blockModel, private PayService $payService)
    {
    }

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

        $cartCollection = new CartCollection($cart);
        $totalSum = TotalService::total($cartCollection);
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

        $addingSchedule = Schedule::findOrFail($params['schedule_id']);

        $schedules = Schedule::whereIn('id', function ($query) use ($user) {
            $query->select('schedule_id')
                ->from('carts')
                ->where('client_id', $user);
        })->get();

        $validSchedule = ScheduleService::scheduleValidation($schedules, $user, $addingSchedule);

        if (!$validSchedule) {
            return response()->json(['message' => 'Invalid schedule'], 400);
        }

        $master = $addingSchedule->master()->first();

        if (!$master->prices()->whereId($params['price_id'])->first()) {
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
            $this->blockModel->unblock($schedule);
        }
        $cartItem->delete();

        return response()->json(['message' => 'Cart item successfully deleted']);
    }

    /**
     * Get way to pay and blocked specified resource by user
     */
    public function checkout(string $user)
    {
        try {
            $cart = $this->index($user)->original['data'];

            if (empty($cart)) {
                throw new Exception('Cart is empty');
            }

            DB::beginTransaction();

            $schedules = Schedule::whereIn('id', function ($query) use ($user) {
                $query->select('schedule_id')
                    ->from('carts')
                    ->where('client_id', $user);
            })->get();

            $validSchedule = ScheduleService::scheduleValidation($schedules, $user);

            if (!$validSchedule) {
                throw new Exception('Invalid schedule');
            }

            $schedules->each(function ($schedule) use ($user){
                $this->blockModel->block(config('constants.db.blocked.minutes'), $user, $schedule);
            });

            $total = TotalService::total($cart['items'], 'payment');

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

    /**
     * Create order and get payment button with link and data for pay order
     */
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

            $schedules = Schedule::whereIn('id', function ($query) use ($userId) {
                $query->select('schedule_id')
                    ->from('carts')
                    ->where('client_id', $userId);
            })->get();

            $validSchedule = ScheduleService::scheduleValidation($schedules, $userId);

            if (!$validSchedule) {
                throw new Exception('Invalid schedule');
            }


            foreach ($schedules as $schedule) {
                if (is_null($schedule->blocked_by) ||
                    ($schedule->blocked_by != $userId) ||
                    ($schedule->blocked_until < now()->setTimezone('Europe/Kiev'))) {
                    throw new Exception('You must checkout first');
                }

                $expired_at = Carbon::createFromDate($schedule->blocked_until, 'Europe/Kiev')->setTimezone('UTC');
            }

            $total = TotalService::total($carts['items'], $params['payment']);

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
            $paidParams['html_button'] = $this->payService->getHtml($total['totalSum'], $order->id, $expired_at, $resultUrl);

            DB::commit();

            return response()->json(['data' => $paidParams]);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 400);

        }
    }
}
