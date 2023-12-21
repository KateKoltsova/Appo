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
            'schedules.date',
            'schedules.time',
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
        $totalSum = TotalSumService::totalSum($cart);
        $cartCollection = CartResource::collection($cart);
        $response = ['data' => []];
        $response['data']['items'] = $cartCollection;
        $response['data']['totalSum'] = $totalSum;
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
                && ($schedule->blocked_until >= now())) ||
            ($schedule->date < now()->format('Y-m-d') ||
                ($schedule->date == now()->format('Y-m-d')
                    && $schedule->time <= now()->format('H:i:s')))) {
            return response()->json(['message' => 'Schedule already unavailable'], 400);
        }

        $inCart = Cart::where('client_id', $user)->pluck('schedule_id')->toArray();
        $otherSchedules = Schedule::whereIn('id', $inCart)->get();

        foreach ($otherSchedules->toArray() as $otherSchedule) {
            if ($otherSchedule['status'] === config('constants.db.status.unavailable') ||
                (!is_null($otherSchedule['blocked_by'])
                    && $otherSchedule['blocked_by'] != $user
                    && !is_null($otherSchedule['blocked_until'])
                    && ($otherSchedule['blocked_until'] >= now())) ||
                ($otherSchedule['date'] < now()->format('Y-m-d') ||
                    ($otherSchedule['date'] == now()->format('Y-m-d')
                        && $otherSchedule['time'] <= now()->format('H:i:s')))) {
                continue;
            }

            $timeInCart = new DateTime($otherSchedule['time']);
            $timeAdding = new DateTime($schedule->time);
            $diff = $timeInCart->diff($timeAdding);
            $diffMinutes = $diff->i + $diff->h * 60;

            if (($otherSchedule['date'] === $schedule->date) && ($diffMinutes === 0)) {
                return response()->json(['message' => 'You already have the same date-time in the cart'], 400);
            }

            if (($otherSchedule['date'] === $schedule->date) && ($diffMinutes < config('constants.db.diff_between_services.minutes'))) {
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
            $totalSum = TotalSumService::totalSum($cart['items'], 'payment');
            $cart['totalSum'] = $totalSum;
            DB::beginTransaction();
            foreach ($cart['items'] as $cartItem) {
                $cartItemSchedule = Schedule::firstWhere('id', $cartItem['schedule_id']);
                if ($cartItemSchedule->status === config('constants.db.status.unavailable') ||
                    (!is_null($cartItemSchedule->blocked_by)
                        && $cartItemSchedule->blocked_by != $user
                        && !is_null($cartItemSchedule->blocked_until)
                        && ($cartItemSchedule->blocked_until >= now())) ||
                    ($cartItemSchedule->date < now()->format('Y-m-d') ||
                        ($cartItemSchedule->date == now()->format('Y-m-d')
                            && $cartItemSchedule->time <= now()->format('H:i:s')))) {
                    return response()->json(['message' => 'Some schedules in cart already unavailable'], 400);
                }
                BlockService::block(config('constants.db.blocked.minutes'), $user, $cartItemSchedule);
            }
            DB::commit();
            return response()->json(['data' => $cart]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Bad request'], 400);
        }
    }

    public function getPayButton(OrderCreateRequest $request, string $user)
    {
        $params = $request->validated();
        $user = User::findOrFail($user);
        $carts = Cart::where('client_id', $user->id)
            ->select([
                'carts.*',
                'prices.price'
            ])
            ->join('prices', 'carts.price_id', '=', 'prices.id')
            ->get();
        $totalSum = TotalSumService::totalSum($carts, $params['payment']);
        $orderParams = [
            'user_id' => $user->id,
            'total' => $totalSum,
            'payment_status' => null
        ];
        $order = Order::create($orderParams);
        $expired_at = $carts->first()->schedule()->first()->blocked_until;
        $resultUrl = $params['result_url'];
        $paidParams['payment'] = $params['payment'];
        $paidParams['html_button'] = LiqpayService::getHtml($totalSum, $order->id, $expired_at, $resultUrl);
        $paidParams['order_id'] = $order->id;
        return $paidParams;
    }
}
