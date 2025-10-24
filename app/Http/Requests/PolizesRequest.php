<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PolizesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'policyNumber' => ['required', 'string'],
            'nit' => ['required', 'string'],
            "customerId" => ['required', 'string'],
            "status" => ['required'],
            "startDate" => ['required'],
            "endDate" => ['required']
        ];
    }
}
