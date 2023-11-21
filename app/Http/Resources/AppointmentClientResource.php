<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            "schedule_id" => $this->schedule_id,
            "service_id" => $this->service_id,
            'sum' => $this->sum,
            'payment' => $this->payment,
            "title" => $this->title,
            "category" => $this->category,
            "master_firstname" => $this->master_firstname,
            "master_lastname" => $this->master_lastname,
            "date" => $this->date,
            "time" => $this->time,
        ];
    }
}
