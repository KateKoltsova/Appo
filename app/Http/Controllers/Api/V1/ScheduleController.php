<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleCreateRequest;
use App\Http\Requests\ScheduleUpdateRequest;
use App\Services\Api\ScheduleService;
use Exception;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(
        private ScheduleService $scheduleService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $user)
    {
        $filters['date'] = $request->input('filter.date');

        return response()->json($this->scheduleService->getList($filters, $user));
    }

    public function getAllAvailable(Request $request)
    {
        $filters['master_id'] = $request->input('filter.master_id');
        $filters['date'] = $request->input('filter.date');
        $filters['category'] = $request->input('filter.category');
        $filters['service_id'] = $request->input('filter.service_id');

        return response()->json($this->scheduleService->getAvailable($filters));
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
    public function store(ScheduleCreateRequest $request, string $user)
    {
        try {
            $params = $request->validated();

            $response = $this->scheduleService->create($params, $user);

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user, string $schedule)
    {
        try {
            $response = $this->scheduleService->getById($user, $schedule);

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
    public function update(ScheduleUpdateRequest $request, string $user, string $schedule)
    {
        try {
            $params = $request->validated();
            $response = $this->scheduleService->update($params, $user, $schedule);

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $user, string $schedule)
    {
        try {
            $this->scheduleService->delete($user, $schedule);

            return response()->json(['message' => 'Schedule successfully deleted']);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function destroyAppointment(string $user, string $schedule)
    {
        try {
            $this->scheduleService->cancelAppointment($user, $schedule);

            return response()->json(['message' => 'Appointment on this schedule successfully deleted']);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }
}
