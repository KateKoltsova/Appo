<?php

namespace Database\Seeders;

use App\Models\Price;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prices = Price::factory()->definition();
        foreach ($prices as $price) {
            Price::updateOrCreate(
                ['master_id' => $price['master_id'], 'service_id' => $price['service_id']],
                ['price' => $price['price']]
            );
        }
    }
}
