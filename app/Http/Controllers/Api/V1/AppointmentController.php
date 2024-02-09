<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentClientCollection;
use App\Http\Resources\AppointmentClientResource;
use App\Models\Appointment;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Schedule;
use App\Repositories\ScheduleRepository;
use App\Services\Contracts\BlockModel;
use App\Services\Contracts\PayService;
use App\Services\ScheduleService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function __construct(private BlockModel         $blockModel,
                                private PayService         $payService,
                                private ScheduleRepository $scheduleRepository)
    {
    }

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
            'schedules.date_time',
        ])
            ->join('schedules', 'appointments.schedule_id', '=', 'schedules.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('users as masters', 'schedules.master_id', '=', 'masters.id')
            ->where('appointments.client_id', '=', $user)
            ->when($date, function ($query) use ($date) {
                $query->whereIn(DB::raw('DATE(date_time)'), $date);
            })
            ->where('date_time', '>', now()->setTimezone('Europe/Kiev'))
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
            $decodedData = $this->payService->getCallback($request);

            if (!$decodedData) {
                throw new Exception('Failed getting callback');
            }

            $order = Order::where('id', $decodedData['order_id'])->first();

            if ($decodedData['amount'] != $order->total) {
                throw new Exception('Invalid amount');
            }

            $order->update([
                'payment_status' => $decodedData['status'],
                'description' => $decodedData['description'],
            ]);

            $params['payment'] = $order->payment;

            if ($order->payment_status != 'success') {
                $message = 'Payment status is ' . $order->payment_status;
                throw new Exception($message);
            }

            $userId = $order->user()->first()->id;

            if (is_null($userId)) {
                $message = 'User not found';
                throw new Exception($message);
            }

            $carts = Cart::where('client_id', $userId)->get();

            if (empty($carts->toArray())) {
                throw new Exception('Cart is empty');
            }

            DB::beginTransaction();

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

                $params['client_id'] = $userId;

                $appointmentParams = [
                    'schedule_id' => $cartItem->schedule_id,
                    'service_id' => $cartItem->service_id,
                    'client_id' => $params['client_id'],
                    'sum' => $params['sum'],
                    'payment' => $params['payment'],
                    'paid_sum' => $params['paid_sum'],
                    'order_id' => $order->id,
                ];

                $newAppointment = Appointment::create($appointmentParams);

                if (is_null($newAppointment)) {
                    throw new Exception('Failed create appointment');
                }

                $schedule = $cartItem->schedule()->first();

                $schedule->update(['status' => config('constants.db.status.unavailable')]);

                $this->blockModel->unblock($schedule);

                $cartItem->delete();
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
            'schedules.date_time',
        ])
            ->join('schedules', 'appointments.schedule_id', '=', 'schedules.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('users as masters', 'schedules.master_id', '=', 'masters.id')
            ->where('appointments.client_id', '=', $user)
            ->where('appointments.id', '=', $appointment)
            ->where('date_time', '>', now()->setTimezone('Europe/Kiev'))
            ->first();

        if (!empty($appointmentInstance)) {
            $appointmentResource = AppointmentClientResource::make($appointmentInstance);
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
