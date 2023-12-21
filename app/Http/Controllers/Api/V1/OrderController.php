<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCreateRequest;
use App\Models\Appointment;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Services\BlockService;
use App\Services\TotalSumService;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(OrderCreateRequest $request, string $userId)
    {
        $params = $request->validated();
        $user = User::findOrFail($userId);
        $carts = Cart::where('client_id', $user->id)->get();
        if (empty($carts->toArray())) {
            return response()->json(['message' => 'Cart is empty'], 404);
        }

        $otherCarts = clone $carts;
        foreach ($carts as $key => $cartItem) {
            $cartItemSchedule = $cartItem->schedule()->first();

            if ($cartItemSchedule->status === config('constants.db.status.unavailable') ||
                (!is_null($cartItemSchedule->blocked_by)
                    && $cartItemSchedule->blocked_by != $user->id
                    && !is_null($cartItemSchedule->blocked_until)
                    && ($cartItemSchedule->blocked_until >= now())) ||
                ($cartItemSchedule->date < now()->format('Y-m-d') ||
                    ($cartItemSchedule->date == now()->format('Y-m-d')
                        && $cartItemSchedule->time <= now()->format('H:i:s')))) {
                return response()->json(['message' => 'Some schedules in cart already unavailable'], 400);
            }

            if (is_null($cartItemSchedule->blocked_by)) {
                return response()->json(['message' => 'You must checkout first'], 400);
            }

            $otherCartsItems = $otherCarts->forget($key);
            if (!is_null($otherCartsItems)) {
                foreach ($otherCartsItems as $otherCartItem) {
                    $otherCartItemSchedule = $otherCartItem->schedule()->first();

                    if ($otherCartItemSchedule->status === config('constants.db.status.unavailable') ||
                        (!is_null($otherCartItemSchedule->blocked_by)
                            && $otherCartItemSchedule->blocked_by != $user->id
                            && !is_null($otherCartItemSchedule->blocked_until)
                            && ($otherCartItemSchedule->blocked_until >= now())) ||
                        ($otherCartItemSchedule->date < now()->format('Y-m-d') ||
                            ($otherCartItemSchedule->date == now()->format('Y-m-d')
                                && $otherCartItemSchedule->time <= now()->format('H:i:s')))) {
                        continue;
                    }

                    $timeCartItem = new DateTime($cartItemSchedule->time);
                    $timeOtherCartItem = new DateTime($otherCartItemSchedule->time);
                    $diff = $timeOtherCartItem->diff($timeCartItem);
                    $diffMinutes = $diff->i + $diff->h * 60;

                    if ($otherCartItemSchedule->date === $cartItemSchedule->date) {
                        if ($diffMinutes === 0) {
                            return response()->json(['message' => 'You already have the same date-time in the cart'], 400);
                        } elseif ($diffMinutes < config('constants.db.diff_between_services.minutes')) {
                            return response()->json(['message' => 'Selected time too close to items in cart'], 400);
                        }
                    }
                }
            }

            if (!$cartItemSchedule->master()->first()->prices()->whereId($cartItem->price_id)->first()) {
                return response()->json(['message' => 'This master does not provide such a service'], 400);
            }
        }
        $totalSum = TotalSumService::totalSum($carts, $params['payment']);
        $orderParams = [
            'user_id' => $user->id,
            'total' => $totalSum,
            'payment_status' => null
        ];
        dd($orderParams);
        $order = Order::create($orderParams);
        try {
            DB::beginTransaction();

            $paymentConfig = config('constants.db.payment');

            foreach ($carts as $cartItem) {
                $params['sum'] = $cartItem->price()->first()->price;

                if (isset($paymentConfig[$params['payment']][1])) {
                    $params['paid_sum'] = $paymentConfig[$params['payment']][1];
                } else {
                    $params['paid_sum'] = $params['sum'];
                }

                $params['client_id'] = $user->id;
                $appointment = [
                    'schedule_id' => $cartItem->schedule_id,
                    'service_id' => $cartItem->service_id,
                    'client_id' => $params['client_id'],
                    'sum' => $params['sum'],
                    'payment' => $params['payment'],
                    'paid_sum' => $params['paid_sum'],
                    'order_id' => $order->id
                ];
                $newAppointment = Appointment::create($appointment);
                $schedules[] = $newAppointment->schedule()->first();
            }

            foreach ($schedules as $schedule) {
                $schedule->update(['status' => config('constants.db.status.unavailable')]);
                BlockService::unblock($schedule);
                Cart::where('schedule_id', $schedule->id)->where('client_id', $user->id)->first()->delete();
            }
            DB::commit();
            return response()->json(['message' => 'Appointment successfully store']);
        } catch (Exception $e) {
            DB::rollBack();
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
