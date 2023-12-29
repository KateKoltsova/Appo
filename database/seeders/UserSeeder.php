<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::admin()->first();
        $admin->users()->create([
            'firstname' => 'Admin',
            'lastname' => 'Admin',
            'birthdate' => fake()->dateTimeBetween('-18 years'),
            'email' => 'admin@admin.com',
            'phone_number' => fake()->unique()->numerify('+380#########'),
            'password' => '11111111',
        ]);
        $i = 0;
        while ($i < 2) {
            $master = Role::master()->first();
            $master->users()->create(User::factory()->definition());
            $i++;
        }
        $i = 0;
        while ($i < 5) {
            $client = Role::client()->first();
            $client->users()->create(User::factory()->definition());
            $i++;
        }
    }
}
