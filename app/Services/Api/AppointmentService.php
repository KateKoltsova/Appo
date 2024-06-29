<?php

namespace App\Services\Api;

use App\Http\Resources\AppointmentClientCollection;
use App\Http\Resources\AppointmentClientResource;
use App\Models\Appointment;
use App\Models\Cart;
use App\Models\Order;
use App\Repositories\AppointmentRepository;
use App\Services\Contracts\BlockModel;
use Exception;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    public function __construct(
        private BlockModel            $blockModel,
        private AppointmentRepository $appointmentRepository,
    )
    {
    }

    /**
     * Display a listing of the appointments.
     */
    public function getList($filterDate, string $userId)
    {
        $appointment = $this->appointmentRepository->getAll($filterDate, $userId);

        $appointmentCollection = new AppointmentClientCollection($appointment);

        return ['data' => $appointmentCollection];
    }

    public function callback($decodedData)
    {
        try {
            DB::beginTransaction();

            if (!$decodedData) {
                throw new Exception('Failed getting callback', 400);
            }

            $order = Order::where('id', $decodedData['order_id'])->first();

            if ($decodedData['amount'] != $order->total) {
                throw new Exception('Invalid amount', 400);
            }

            $order->update([
                'payment_status' => $decodedData['status'],
                'description' => $decodedData['description'],
            ]);

            if ($order->payment_status != 'success') {
                throw new Exception('Payment status is ' . $order->payment_status, 400);
            }

            DB::commit();

            return true;

        } catch (Exception $e) {
            DB::rollBack();

            $order->update(['description' => $e->getMessage()]);

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create(int $orderId, int $userId)
    {
        try {
            $order = Order::where('id', $orderId)->where('user_id', $userId)->first();

            if (empty($order->toArray())) {
                throw new Exception('Order not found', 404);
            }

            $order->update([
                'payment_status' => 'wait for pay',
                'description' => 'Order waiting for successful payment',
            ]);

            $params['payment'] = $order->payment;

            $carts = Cart::where('client_id', $userId)->get();

            if (empty($carts->toArray())) {
                throw new Exception('Cart is empty', 400);
            }

            DB::beginTransaction();

            foreach ($carts as $cartItem) {

                $params['sum'] = $cartItem->price()->first()->price;

                if (is_null($params['sum'])) {
                    throw new Exception('Cart item has no price', 400);
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
                    throw new Exception('Failed create appointment', 400);
                }

                $schedule = $cartItem->schedule()->first();

                $schedule->update(['status' => config('constants.db.status.unavailable')]);

                $this->blockModel->unblock($schedule);

                $cartItem->delete();
            }

            DB::commit();

            return true;

        } catch (Exception $e) {
            DB::rollBack();

            $order->update(['description' => $e->getMessage()]);

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Display the specified appointment.
     */
    public function getById(string $userId, string $appointmentId)
    {
        try {
            $appointment = $this->appointmentRepository->getById($userId, $appointmentId);

            if (!empty($appointment)) {
                return ['data' => AppointmentClientResource::make($appointment)];
            } else {
                throw new Exception('Appointment not found', 404);
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function delete(string $userId, string $appointmentId)
    {
        try {
            $appointment = Appointment::where('id', $appointmentId)->where('client_id', $userId)->first();

            if (!$appointment) {
                throw new Exception('Appointment not found', 404);
            }

            $appointment->schedule()->first()->update(['status' => config('constants.db.status.available')]);
            $appointment->delete();

            return true;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

}
