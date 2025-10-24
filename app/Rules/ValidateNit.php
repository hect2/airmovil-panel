<?php

namespace App\Rules;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Rule;
use GuzzleHttp\Exception\RequestException;

class ValidateNit implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        try {

            $client = new Client(['timeout' => 5]);

            $loginResponse = $client->post('https://dev.universales.com/api-externas/public/login', [
                'json' => [
                    'email' => env('EMAIL_UNIVERSALES'),
                    'password' => env('PASSWORD_UNVERSALES'),
                ],
                'http_errors' => false,
            ]);

            $loginData = json_decode($loginResponse->getBody(), true);

            if (
                !isset($loginData['code']) || 
                $loginData['code'] != 200 ||
                empty($loginData['recordset']['JWToken'])
            ) {
                return false;
            }

            $token = $loginData['recordset']['JWToken'];

            $nitResponse = $client->get("https://dev.universales.com/api-externas/public/contacto/{$value}", [
                'headers' => [
                    'Authorization' => "Bearer {$token}"
                ],
                'http_errors' => false,
            ]);

            $data = json_decode($nitResponse->getBody(), true);

            if (
                isset($data['code']) && $data['code'] == 200 &&
                isset($data['recordset']['descripcion']) &&
                strtolower($data['recordset']['descripcion']) === 'activo'
            ) {
                return true;
            }

            return false;
            
        } catch (RequestException $exception) {
            return false;
        }
        
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Lo lamento has ingresado un nit incorrecto';
    }
}
