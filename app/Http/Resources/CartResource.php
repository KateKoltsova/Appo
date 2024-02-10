<?php

namespace App\Http\Resources;

use App\Services\Api\ScheduleService;
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

        $userId = $this->user()->first()->id;
        $thisSchedule = $this->resource;

        $appointments = $this->user()->first()->appointments()->get();

        $appointmentSchedulesValidation = $appointments->every(function ($appointment) use ($userId, $thisSchedule) {
            $schedule = $appointment->schedule()->first();
            return ScheduleService::isValidDateTime($thisSchedule, $schedule);
        });

        if (!ScheduleService::isAvailable($thisSchedule, $userId)) {
            $cart['message'] = 'Schedule already unavailable';
        } elseif (!$appointmentSchedulesValidation) {
            $cart['message'] = 'You can\'t make appointment to that date time';
        }

        return $cart;
    }
}
