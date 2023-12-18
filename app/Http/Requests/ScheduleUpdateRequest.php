<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleUpdateRequest extends FormRequest
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
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i:s', function ($attribute, $value, $fail) {
                if ($this->date == now()->format('Y-m-d')) {
                    if ($value <= now()->format('H:i:s')) {
                        $fail("The time field must be after now");
                    }
                }
            },
                Rule::unique('schedules', 'time')
                    ->where('master_id', $this->user()->id)
                    ->where('date', $this->date)
                    ->ignore($this->schedule)]
        ];
    }
}
