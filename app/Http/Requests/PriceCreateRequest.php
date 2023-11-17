<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriceCreateRequest extends FormRequest
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
        $role = Role::master()->first();
        $masters = $role->users()->pluck('id')->toArray();
        $services = Service::pluck('id')->toArray();
        return [
            'master_id' => ['required', Rule::in($masters)],
            'service_id' => ['required', Rule::in($services)],
            'price' => ['required', 'integer', 'min:1']
        ];
    }
}
