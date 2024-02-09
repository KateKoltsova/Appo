<?php

namespace App\Repositories;

use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class AppointmentRepository
{
    public function __construct(protected Appointment $model)
    {
    }

    public function getAll($date, string $userId)
    {
        return $this->model->select([
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
            ->where('appointments.client_id', '=', $userId)
            ->when($date, function ($query) use ($date) {
                $query->whereIn(DB::raw('DATE(date_time)'), $date);
            })
            ->where('date_time', '>', now()->setTimezone('Europe/Kiev'))
            ->get();
    }

    public function getById(string $userId, string $appointmentId)
    {
        return $this->model->select([
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
            ->where('appointments.client_id', '=', $userId)
            ->where('appointments.id', '=', $appointmentId)
            ->where('date_time', '>', now()->setTimezone('Europe/Kiev'))
            ->first();
    }

}
