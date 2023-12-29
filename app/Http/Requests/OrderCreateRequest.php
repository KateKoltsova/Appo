<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderCreateRequest extends FormRequest
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
        $paymentConfig = config('constants.db.payment');
        $payments = array_map(function ($value) {
            return $value[0];
        }, $paymentConfig);
        return [
            'payment' => ['required', Rule::in($payments)],
            'result_url' => ['required']
        ];
    }
}
