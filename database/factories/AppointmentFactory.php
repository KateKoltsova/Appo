<?php

namespace Database\Factories;

use App\Models\Price;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $schedule = Schedule::inRandomOrder()->first();
        $service = Service::inRandomOrder()->first();
        $client = Role::client()->first()->users()->inRandomOrder()->first();
        $sum = Price::get()
            ->where('master_id', '=', $schedule->master_id)
            ->where('service_id', '=', $service->id)
            ->first();
        $wayToPay = config('constants.db.payment');
        $wayToPay = $wayToPay[array_rand($wayToPay)];
        $payment = $wayToPay[0];
        $paid_sum = $wayToPay[1] ?? $sum->price;
        $status = config('constants.db.status.unavailable');
        return [
            'schedule_id' => $schedule->id,
            'status' => $status,
            'service_id' => $service->id,
            'client_id' => $client->id,
            'sum' => $sum->price,
            'payment' => $payment,
            'paid_sum' => $paid_sum
        ];
    }
}
