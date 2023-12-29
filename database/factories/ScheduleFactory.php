<?php

namespace Database\Factories;

use App\Models\Role;
use DateInterval;
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

        $startDate = new DateTime('tomorrow 8:00', new DateTimeZone('Europe/Kiev'));
        $endDate = new DateTime('last day of this month 18:00', new DateTimeZone('Europe/Kiev'));

        $interval = new DateInterval('PT2H'); // интервал 2 часа

        $currentDate = clone $startDate;
        $dates = [];

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('Y-m-d H:i:s');
            $currentDate->add($interval);
        }

        $result = [];
        foreach ($masters as $master) {
            foreach ($dates as $date) {
                $result[] = [
                    'master_id' => $master->id,
                    'date_time' => $date,
                    'status' => $status
                ];
            }
        }
        return $result;
    }
}
