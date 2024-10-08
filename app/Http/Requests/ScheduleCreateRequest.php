<?php

namespace App\Http\Requests;

use DateTime;
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
                Rule::unique('schedules')->where('master_id', $this->user()->id),
                function ($attribute, $value, $fail) {
                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
                    if ($date && $date->format('s') !== '00') {
                        $fail('The ' . $attribute . ' must have seconds set to 00.');
                    }
                }
            ]
        ];
    }
}
