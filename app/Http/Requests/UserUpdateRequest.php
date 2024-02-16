<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
        if (isset($this->phone_number) && (Str::substr($this->phone_number, 0, 1) != '+')) {
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
            'firstname' => ['string', 'alpha', 'max:50'],
            'lastname' => ['string', 'alpha', 'max:50'],
            'birthdate' => ['nullable', 'date', 'before_or_equal:-18 years'],
            'email' => ['string', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'phone_number' => ['string', 'min:13', 'max:13', 'regex:/^\+380[0-9]{9}$/', Rule::unique('users')->ignore($this->user()->id)],
            'image' => ['image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ];
    }
}
