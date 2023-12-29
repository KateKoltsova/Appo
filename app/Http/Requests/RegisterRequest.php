<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (Str::substr($this->phone_number, 0, 1) != '+') {
            $this->merge([
                'phone_number' => '+' . $this->phone_number,
            ]);
        }
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'max:50'],
            'lastname' => ['required', 'string', 'max:50'],
            'birthdate' => ['nullable', 'date', 'before_or_equal:-18 years'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:App\Models\User'],
            'phone_number' => ['required', 'string', 'min:13', 'max:13', 'regex:/^\+380[0-9]{9}$/', 'unique:App\Models\User'],
            'password' => ['required', 'string', 'min:3'],
        ];
    }
}
