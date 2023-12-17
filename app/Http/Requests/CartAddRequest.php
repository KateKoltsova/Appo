<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartAddRequest extends FormRequest
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
            'schedule_id' => ['required', 'exists:App\Models\Schedule,id', Rule::unique('carts', 'schedule_id')
                ->where(function ($query) {
                return $query->where('client_id', $this->user()->id);
            })],
            'service_id' => ['required', Rule::exists('prices', 'service_id')->where('id', $this->price_id)],
            'price_id' => ['required', 'exists:App\Models\Price,id']
        ];
    }
}
