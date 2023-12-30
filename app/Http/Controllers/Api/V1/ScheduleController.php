<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleCreateRequest;
use App\Http\Requests\ScheduleUpdateRequest;
use App\Http\Resources\AvailableScheduleCollection;
use App\Http\Resources\AvailableScheduleResource;
use App\Http\Resources\ScheduleResource;
use App\Models\Role;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $user)
    {
        $date = $request->input('filter.date');
        $schedule = Schedule::select([
            'schedules.id as schedule_id',
            'schedules.*'
        ])
            ->with(['appointment' => function ($query) {
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
            ->where('date_time', '>', now()->setTimezone('Europe/Kiev'))
            ->when($date, function ($query) use ($date) {
                $query->whereIn(DB::raw('DATE(date_time)'), $date);
            })
            ->get();

        $scheduleCollection = ScheduleResource::collection($schedule);

        return response()->json(['data' => $scheduleCollection]);
    }

    public function getAllAvailable(Request $request)
    {
        $master_id = $request->input('filter.master_id');
        $date = $request->input('filter.date');
        $category = $request->input('filter.category');
        $service = $request->input('filter.service_id');

        $availableSchedules = Role::master()->first()->users()
            ->select([
                'id',
                'firstname',
                'lastname'
            ])
            ->when($master_id, function ($query) use ($master_id) {
                $query->where('id', $master_id);
            })
            ->with([
                'schedules' => function ($query) use ($date) {
                    $query
                        ->where('schedules.status', config('constants.db.status.available'))
                        ->where(function ($query) {
                            $query
                                ->where('schedules.blocked_until', '<', now())
                                ->orWhereNull('schedules.blocked_until');
                        })
                        ->where('date_time', '>', now()->setTimezone('Europe/Kiev'))
                        ->when($date, function ($query) use ($date) {
                            $query->whereIn(DB::raw('DATE(date_time)'), $date);
                        })
                        ->select([
                            'id as schedule_id',
                            'master_id',
                            'date_time',
                            'status',
                        ]);
                }
            ])
            ->with([
                'prices' => function ($query) use ($service, $category) {
                    $query
                        ->join('services', 'prices.service_id', '=', 'services.id')
                        ->when($service, function ($query) use ($service) {
                            $query->whereIn('service_id', $service);
                        })
                        ->when($category, function ($query) use ($category) {
                            $query->whereIn('category', $category);
                        })
                        ->select([
                            'prices.id as price_id',
                            'master_id',
                            'service_id',
                            'price',
                            'category',
                            'title'
                        ]);
                }
            ])
            ->get();

        $availableScheduleCollection = new AvailableScheduleCollection($availableSchedules);

        return response()->json(['data' => $availableScheduleCollection]);
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
        $params = $request->validated();
        $schedule = Schedule::create(
            [
                'master_id' => $user,
                'date_time' => $params['date_time'],
                'status' => config('constants.db.status.available')
            ],
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
        $scheduleInstance = Schedule::select([
            'schedules.id as schedule_id',
            'schedules.*'
        ])
            ->with(['appointment' => function ($query) {
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
            ->where('date_time', '>', now()->setTimezone('Europe/Kiev'))
            ->where('master_id', $user)
            ->where('schedules.id', $schedule)
            ->first();

        if (!empty($scheduleInstance)) {
            $scheduleResource = ScheduleResource::make($scheduleInstance);
            return response()->json(['data' => $scheduleResource]);
        } else {
            return response()->json(['message' => 'Schedule not found'], 404);
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
        $scheduleInstance = Schedule::where('id', $schedule)->where('master_id', $user)->first();

        if ($scheduleInstance) {
            $scheduleInstance->update($params);
            return $this->show($user, $schedule);
        } else {
            return response()->json(['message' => 'Schedule not found'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $user, string $schedule)
    {
        $scheduleInstance = Schedule::where('id', $schedule)->where('master_id', $user)->first();

        if (!$scheduleInstance) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }

        if ($scheduleInstance->status == config('constants.db.status.unavailable')) {
            $scheduleInstance->appointment()->first()->delete();
        }

        $scheduleInstance->delete();

        return response()->json(['message' => 'Schedule successfully deleted']);
    }

    public function destroyAppointment(string $user, string $schedule)
    {
        $scheduleInstance = Schedule::where('id', $schedule)->where('master_id', $user)->first();

        if (!$scheduleInstance) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }

        if ($scheduleInstance->status == config('constants.db.status.unavailable')) {
            $scheduleInstance->appointment()->first()->delete();
            $scheduleInstance->update(['status' => config('constants.db.status.available')]);
        }

        return response()->json(['message' => 'Appointment on this schedule successfully deleted']);
    }
}
