<?php

namespace Database\Factories;

use App\Models\Role;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = config('constants.db.status.available');
        $role = Role::master()->first();
        $masters = $role->users()->get();
        $date = new DateTime('now', new DateTimeZone('Europe/Kiev'));
        $daysInMonth = $date->format('t');

        for ($day = 1; $day <= $daysInMonth; $day += 2) {
            $dates[] = date('Y-m-') . $day;
        }

        $times = [
            fake()->time('H') . ':00:00',
            fake()->time('H') . ':00:00',
            fake()->time('H') . ':00:00'
        ];

        $result = [];
        foreach ($masters as $master) {
            foreach ($dates as $date) {
                foreach ($times as $time) {
                    $result[] = [
                        'master_id' => $master->id,
                        'date' => $date,
                        'time' => $time,
                        'status' => $status
                    ];
                }
            }
        }
        return $result;
    }
}
