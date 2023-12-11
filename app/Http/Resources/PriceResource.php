<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'service_id' => $this->service_id,
            'price_id' => $this->price_id,
            'title' => $this->title,
            'category' => $this->category,
            'price' => $this->price,
        ];
    }
}
