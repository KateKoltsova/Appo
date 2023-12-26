<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i:s',
                function ($attribute, $value, $fail) {
                    $combinedDateTimeString = $this->date . ' ' . $value;
                    $combinedDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $combinedDateTimeString, 'Europe/Kiev');

                    $utcCombinedDateTime = $combinedDateTime->setTimezone('UTC');

                    if ($utcCombinedDateTime->format('Y-m-d') < now()->format('Y-m-d')) {
                        $fail("The date field must be after now");
                    }

                    if ($utcCombinedDateTime->format('Y-m-d') == now()->format('Y-m-d')) {
                        if ($utcCombinedDateTime->format('H:i:s') <= now()->format('H:i:s')) {
                            $fail("The time field must be after now");
                        }
                    }

                    $exists = Schedule::where('master_id', $this->user()->id)
                        ->where('date', $utcCombinedDateTime->format('Y-m-d'))
                        ->where('time', $utcCombinedDateTime->format('H:i:s'))
                        ->where('id', '<>', $this->schedule)
                        ->exists();

                    if ($exists) {
                        $fail("The combination of date and time must be unique.");
                    }
                },
            ]
        ];
    }
}
