<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointment = Appointment::factory()->definition();
        Schedule::where('id', '=', $appointment['schedule_id'])
            ->update(['status' => $appointment['status']]);
        unset($appointment['status']);
        Appointment::create($appointment);
    }
}
