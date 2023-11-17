<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleCreateRequest;
use App\Http\Requests\ScheduleUpdateRequest;
use App\Http\Resources\ScheduleCollection;
use App\Http\Resources\ScheduleResource;
use App\Models\Appointment;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $user)
    {
        $date = $request->input('filter.date');
        $schedule = Schedule::with([
            'appointment' => function ($query) {
                $query
                    ->join('users', 'appointments.client_id', '=', 'users.id')
                    ->join('services', 'appointments.service_id', '=', 'services.id')
                    ->select([
                        'users.id as users_id',
                        'users.*',
                        'services.id as services_id',
                        'services.*',
                        'appointments.id as appointments_id',
                        'appointments.*',
                    ]);
            }
        ])
            ->where('master_id', $user)
            ->when($date, function ($query) use ($date) {
                $query->whereIn('date', $date);
            })
            ->get();
        if (!empty($schedule->toArray())) {
            $scheduleCollection = new ScheduleCollection($schedule);
            return response()->json(['data' => $scheduleCollection]);
        } else {
            return response()->json(['message' => 'No data'], 404);
        }
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
    public function store(ScheduleCreateRequest $request)
    {
        $params = $request->validated();
        $schedule = Schedule::updateOrCreate(
            [
                'master_id' => $params['master_id'],
                'date' => $params['date'],
                'time' => $params['time'],
            ],
            ['status' => config('constants.db.status.available')]
        );
        if ($schedule) {
            return $this->show($schedule->master_id, $schedule->id);
        } else {
            return response()->json(['message' => 'Bad request'], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user, string $schedule)
    {
        $scheduleInstance = Schedule::with([
            'appointment' => function ($query) {
                $query
                    ->join('users', 'appointments.client_id', '=', 'users.id')
                    ->join('services', 'appointments.service_id', '=', 'services.id')
                    ->select([
                        'users.id as users_id',
                        'users.*',
                        'services.id as services_id',
                        'services.*',
                        'appointments.id as appointments_id',
                        'appointments.*',
                    ]);
            }
        ])
            ->where('master_id', $user)
            ->where('schedules.id', $schedule)
            ->first();
        if (!empty($scheduleInstance)) {
            $scheduleResource = new ScheduleResource($scheduleInstance);
            return response()->json(['data' => $scheduleResource]);
        } else {
            return response()->json(['message' => 'No data'], 404);
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
        $params = $request->validated();
        $scheduleInstance = Schedule::where('id', $schedule);
        if ($scheduleInstance) {
            $scheduleInstance->update($params);
            return $this->show($user, $schedule);
        } else {
            return response()->json(['message' => 'No data'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $user, string $schedule)
    {
        $scheduleInstance = Schedule::where('id', $schedule)->first();
        if (!$scheduleInstance) {
            return response()->json(['message' => 'No data'], 404);
        }
        if ($scheduleInstance->status == 'unavailable') {
            Appointment::where('schedule_id', $scheduleInstance->id)->delete();
        }
        $scheduleInstance->delete();
        return response()->json(['message' => 'Schedule successfully deleted']);
    }
}
