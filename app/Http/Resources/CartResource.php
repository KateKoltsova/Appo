<?php

namespace App\Http\Resources;

use App\Models\Appointment;
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
            'date' => $this->date,
            'time' => $this->time,
        ];

        if ($this->status === config('constants.db.status.unavailable') ||
            (!is_null($this->blocked_by)
                && $this->blocked_by != $this->user()->first()->id
                && !is_null($this->blocked_until)
                && ($this->blocked_until >= now())) ||
            ($this->date < now()->format('Y-m-d') ||
                ($this->date == now()->format('Y-m-d')
                    && $this->time <= now()->format('H:i:s')))) {
            $cart['message'] = 'Schedule already unavailable';
        }

        return $cart;
    }
}
