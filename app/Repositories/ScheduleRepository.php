<?php

namespace App\Repositories;

use App\Models\Schedule;

class ScheduleRepository
{
    public function __construct(protected Schedule $model)
    {
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
