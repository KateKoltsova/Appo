<?php

namespace App\Http\Resources;

use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cart = [
            'id' => $this->id,
            'schedule_id' => $this->schedule_id,
            'service_id' => $this->service_id,
            'price_id' => $this->price_id,
            'price' => $this->price,
            'title' => $this->title,
            'category' => $this->category,
            'master_firstname' => $this->master_firstname,
            'master_lastname' => $this->master_lastname,
            'date_time' => $this->date_time,
            'status' => $this->status
        ];

        if (!ScheduleService::isAvailable($this->resource, $this->user()->first()->id)) {
            $cart['message'] = 'Schedule already unavailable';
        }

        return $cart;
    }
}
