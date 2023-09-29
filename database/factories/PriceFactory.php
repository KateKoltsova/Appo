<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = Role::master()->first();
        $masters = $role->users()->get();
        $services = Service::get('id')->sortBy('id');
        $result = [];
        foreach ($masters as $master) {
            foreach ($services as $service) {
                $result[] = [
                    'master_id' => $master->id,
                    'service_id' => $service->id,
                    'price' => fake()->numberBetween(1,99) . '0',
                ];
            }
        }
        return $result;
    }
}
