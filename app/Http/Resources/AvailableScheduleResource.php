<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailableScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (!empty($this->schedules[0]) && !empty($this->prices[0])) {
            $availableSchedule = [
                'master_id' => $this->id,
                'master_firstname' => $this->firstname,
                'master_lastname' => $this->lastname,
                'schedules' => ScheduleResource::collection($this->schedules),
                'prices' => PriceResource::collection($this->prices)
            ];
        } else {
            $availableSchedule = [];
        }
        return $availableSchedule;
    }
}
