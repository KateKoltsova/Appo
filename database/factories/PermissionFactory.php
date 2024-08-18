<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $permissionsConfig = config('constants.db.permissions');
        foreach ($permissionsConfig['instances'] as $instance) {
            foreach ($permissionsConfig['actions'] as $action) {
                $permissions[] = $instance . '.' . $action;
            }
        }

        return $permissions;
    }
}
