<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (!empty($this->appointment)) {
            $schedule = [
                'schedule_id' => $this->schedule_id,
                'date_time' => $this->date_time,
                'status' => $this->status,
                'appointment' => new AppointmentResource($this->appointment)
            ];
        } else {
            $schedule = [
                'schedule_id' => $this->schedule_id,
                'date_time' => $this->date_time,
                'status' => $this->status,
            ];
        }
        return $schedule;
    }
}
