<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\AppointmentService;
use App\Services\Contracts\PayService;
use Exception;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        private PayService         $payService,
        private AppointmentService $appointmentService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $user)
    {
        $filterDate = $request->input('filter.date');

        return response()->json($this->appointmentService->getList($filterDate, $user));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function callback(Request $request)
    {
        try {
            $decodedData = $this->payService->getCallback($request);

            $this->appointmentService->callback($decodedData);

            return response()->json(['message' => 'Order payment status updated']);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $user)
    {
        try {
            $orderId = $request->input('order_id');

            $this->appointmentService->create($orderId, $user);

            return response()->json(['message' => 'Appointment successfully created']);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user, string $appointment)
    {
        try {
            $response = $this->appointmentService->getById($user, $appointment);

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
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
        try {
            $this->appointmentService->delete($user, $appointment);

            return response()->json(['message' => 'Appointment successfully deleted']);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }
}
