<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleOwnerRequest extends FormRequest
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
            'name'                  => ['required', 'string', 'max:190'],
            'email'                 => [
                'required',
                'email',
                'max:190'
            ],
            'phone'                 => [
                'nullable',
                'string',
                'max:20'
            ],
            'status'                => ['required', 'string', 'max:24'],
            // 'country_code'          => ['required', 'string', 'max:20'],
            // "nit"          => ["min:1"],
            // "lastName"     => ["min:1"],
            // "documentType"      => ["min:1"],
            // "documentId"   => ["min:1"],
            // "birthDate"    => ["min:1"],
            // "country"      => ["min:1"],
            // "mobile"       => ["min:1"],
            // "gender"       => ["min:1"],
            // "zone"         => ["min:1"],
            // "address"      => ["min:1"],
            // "department"   => ["min:1"],
            // "municipality" => ["min:1"],
            // "contactCode"  => ["min:1"],
        ];
    }
}
