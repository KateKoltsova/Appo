<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoadGalleryRequest extends FormRequest
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
            'images' => ['required', 'array'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        $messages = [];
        if (empty($this->images)) {
            $messages['images.required'] = 'The images field is required.';
        } else {
            foreach ($this->images as $key => $image) {
                $messages["images.{$key}.required"] = "The {$image->getClientOriginalName()} field is required.";
                $messages["images.{$key}.image"] = "The {$image->getClientOriginalName()} field must be an image.";
                $messages["images.{$key}.mimes"] = "The {$image->getClientOriginalName()} field must be a file of type: jpeg, png, jpg.";
                $messages["images.{$key}.max"] = "The {$image->getClientOriginalName()} field may not be greater than 5 MB.";
            }
        }
        return $messages;
    }
}
