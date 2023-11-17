<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $services = config('constants.db.services');
        $result = [];
        foreach ($services as $service) {
            foreach ($service['title'] as $item) {
                $result[$item] = $service['category'];
            }
        }
        return $result;
    }
}
