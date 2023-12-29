<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'date_time' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after:' . now()->setTimezone('Europe/Kiev')->toDateTimeString(),
                Rule::unique('schedules')->where('master_id', $this->user()->id)
            ]
        ];
    }
}
