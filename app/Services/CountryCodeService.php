<?php

namespace App\Services;


use Exception;
use Illuminate\Support\Facades\Log;
use PragmaRX\Countries\Package\Countries;

class CountryCodeService
{

    /**
     * @throws Exception
     */
    public function list(): array
    {
        try {
            // Simulación de la lista de países
            $countryArray = [
                (object)[
                    'country_code' => 'GT',
                    'country_name' => 'Guatemala (GT)',
                ]
                // Agrega más países simulados aquí si lo deseas
            ];

            return ['data' => $countryArray];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show($country)
    {
        try {
            // Simulación de la respuesta para un país específico
            $simulatedCountryData = null;

            $simulatedCountryData = (object)[
                'calling_code'  => '+502',
                'cca3' => 'GTM',
                'admin' => 'Guatemala',
                'flag' => (object)[
                    'emoji' => 'GT',
                    'svg' => '<svg>...</svg>',
                    'svg_path' => '/images/language/guatemala.png',
                ],
                'capital_rinvex' => 'Ciudad de Guatemala',
                'demonym' => 'Guatemalan',
            ];

            return $simulatedCountryData;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
