<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = Service::factory()->definition();
        foreach ($services as $service => $category) {
            Service::firstOrCreate([
                'title' => $service,
                'category' => $category
            ]);
        }
    }
}
