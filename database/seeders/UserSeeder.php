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
