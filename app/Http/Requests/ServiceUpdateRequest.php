<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceUpdateRequest extends FormRequest
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
            'title' => ['string', 'max:200', Rule::unique('services')->ignore($this->service)],
            'description' => ['string', 'max:1000'],
            'category' => [
                function ($attribute, $value, $fail) {
                    $categoryExists = Service::where('category', $value)->exists();

                    if (!$categoryExists && strlen($value) > 100) {
                        $fail('The ' . $attribute . ' must be an existing category or a new category with a maximum length of 100 characters.');
                    }
                }
            ],
            'image' => ['image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ];
    }
}
