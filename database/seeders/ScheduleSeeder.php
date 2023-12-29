<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = Schedule::factory()->definition();
        foreach ($schedules as $schedule) {
            Schedule::updateOrCreate(
                [
                    'master_id' => $schedule['master_id'],
                    'date_time' => $schedule['date_time'],
                ],
                ['status' => $schedule['status']]
            );
        }
    }
}
