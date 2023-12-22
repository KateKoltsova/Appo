<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentCreateRequest;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Resources\AppointmentClientCollection;
use App\Http\Resources\AppointmentClientResource;
use App\Models\Appointment;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Services\BlockService;
use App\Services\LiqpayService;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $user)
    {
        $date = $request->input('filter.date');
        $appointment = Appointment::select([
            'appointments.*',
            'services.title',
            'services.category',
            'masters.firstname as master_firstname',
            'masters.lastname as master_lastname',
            'schedules.date',
            'schedules.time',
        ])
            ->join('schedules', 'appointments.schedule_id', '=', 'schedules.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('users as masters', 'schedules.master_id', '=', 'masters.id')
            ->where('appointments.client_id', '=', $user)
            ->when($date, function ($query) use ($date) {
                $query->whereIn('date', $date);
            })
            ->where(function ($query) {
                $query
                    ->where('date', '>', now())
                    ->orWhere('date', '=', now()->format('Y-m-d'))
                    ->where('time', '>', now()->format('H:i:s'));
            })
            ->get();
        $appointmentCollection = new AppointmentClientCollection($appointment);
        return response()->json(['data' => $appointmentCollection]);
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
    public function store(Request $request)
    {
        try {
            $data = $request->input('data');
            $signature = $request->input('signature');

            $privateKey = env('LIQPAY_TEST_PRIVATE_KEY');
            $expectedSignature = base64_encode(sha1($privateKey . $data . $privateKey, true));

            if ($signature !== $expectedSignature) {
                throw new Exception('Invalid signature');
            }

            $decodedData = json_decode(base64_decode($data), true);

            $order = Order::where('id', $decodedData['order_id'])->first();

            if ($decodedData['amount'] != $order->total) {
                throw new Exception('Invalid amount');
            }

            $order->update(['payment_status' => $decodedData['status']]);


            if ($order->payment_status != 'success') {
                $message = 'Payment status is ' . $order->payment_status;
                throw new Exception($message);
            }

            $user = $order->user()->first();

            if (is_null($user)) {
                $message = 'User not found';
                throw new Exception($message);
            }

            $carts = Cart::where('client_id', $user->id)->get();

            if (is_null($carts)) {
                throw new Exception('Carts not found');
            }

            if (empty($carts->toArray())) {
                throw new Exception('Cart is empty');
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
                    throw new Exception('Some schedules in cart already unavailable');
                }

                if (is_null($cartItemSchedule->blocked_by)) {
                    throw new Exception('You must checkout first');
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
                                throw new Exception('You already have the same date-time in the cart');
                            } elseif ($diffMinutes < config('constants.db.diff_between_services.minutes')) {
                                throw new Exception('Selected time too close to items in cart');

                            }
                        }
                    }
                }

                if (!$cartItemSchedule->master()->first()->prices()->whereId($cartItem->price_id)->first()) {
                    throw new Exception('This master does not provide such a service');
                }
            }

            DB::beginTransaction();

            $params['payment'] = $order->payment;

            foreach ($carts as $cartItem) {
                $params['sum'] = $cartItem->price()->first()->price;

                if (is_null($params['sum'])) {
                    throw new Exception('Cart item has no price');
                }

                $paymentConfig = config('constants.db.payment');

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

                if (is_null($newAppointment)) {
                    throw new Exception('Failed create appointment');
                }

                $newAppointmentSchedule = $newAppointment->schedule()->first();

                if (is_null($newAppointmentSchedule)) {
                    throw new Exception('Failed get schedule for appointment id ' . $newAppointment->id);
                }

                $schedules[] = $newAppointmentSchedule;
            }

            if (is_null($schedules)) {
                throw new Exception('Failed get schedules for appointments');
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
            $order->update(['description' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user, string $appointment)
    {
        $appointmentInstance = Appointment::select([
            'appointments.*',
            'services.title',
            'services.category',
            'masters.firstname as master_firstname',
            'masters.lastname as master_lastname',
            'schedules.date',
            'schedules.time',
        ])
            ->join('schedules', 'appointments.schedule_id', '=', 'schedules.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('users as masters', 'schedules.master_id', '=', 'masters.id')
            ->where('appointments.client_id', '=', $user)
            ->where('appointments.id', '=', $appointment)
            ->where(function ($query) {
                $query
                    ->where('date', '>', now())
                    ->orWhere('date', '=', now()->format('Y-m-d'))
                    ->where('time', '>', now()->format('H:i:s'));
            })
            ->first();
        if (!empty($appointmentInstance)) {
            $appointmentResource = new AppointmentClientResource($appointmentInstance);
            return response()->json(['data' => $appointmentResource]);
        } else {
            return response()->json(['message' => 'Appointment not found'], 404);
        }
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
    public function destroy(string $user, string $appointment)
    {
        $appointmentInstance = Appointment::where('id', $appointment)->where('client_id', $user)->first();

        if (!$appointmentInstance) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $appointmentInstance->schedule()->first()->update(['status' => config('constants.db.status.available')]);
        $appointmentInstance->delete();
        return response()->json(['message' => 'Appointment successfully deleted']);
    }
}
