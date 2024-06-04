<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class ScheduleRepository
{
    public function __construct(protected Schedule $model)
    {
    }

    public function getAll($filters, string $userId)
    {
        return $this->model->select([
            'schedules.id as schedule_id',
            'schedules.*',
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
            },
            ])
            ->where('master_id', $userId)
            ->where('date_time', '>', now()->setTimezone('Europe/Kiev'))
            ->when($filters['date'], function ($query) use ($filters) {
                $query->whereIn(DB::raw('DATE(date_time)'), $filters['date']);
            })
            ->get();
    }

    public function getByIdWithAppointments($userId, $scheduleId)
    {
        return $this->model->select([
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
            ->where('master_id', $userId)
            ->where('schedules.id', $scheduleId)
            ->first();
    }

    public function getAvailableWithFilters($filters)
    {
        return Role::master()->first()->users()
            ->select([
                'id',
                'firstname',
                'lastname',
                'image_url'
            ])
            ->when($filters['master_id'], function ($query) use ($filters) {
                $query->where('id', $filters['master_id']);
            })
            ->withSchedules($filters['date'])
            ->withPrices($filters['service_id'], $filters['category'])
            ->get();
    }

    public function getSchedulesInCartByUserId($userId)
    {
        return Schedule::whereIn('id', function ($query) use ($userId) {
            $query->select('schedule_id')
                ->from('carts')
                ->where('client_id', $userId);
        })->get();
    }

    public function getAppointmentSchedulesByUserId($userId)
    {
        return Schedule::whereIn('id', function ($query) use ($userId) {
            $query->select('schedule_id')
                ->from('appointments')
                ->where('client_id', $userId);
        })->get();
    }
}
