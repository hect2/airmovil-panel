<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentBacService
{
    // -------------------------------------------------------------------------
    // HELPERS PRIVADOS
    // -------------------------------------------------------------------------

    /**
     * Valida los campos requeridos del pago.
     */
    private static function validatePaymentData(array $data, array $validations): array
    {
        $validator = Validator::make($data, $validations);

        if ($validator->fails()) {
            return [
                'Code' => '400',
                'data' => [
                    'Message' => 'Error de validación.',
                    'Errors' => $validator->errors(),
                ],
            ];
        }

        return [];
    }

    /**
     * Construye la URL completa según el método y si usa SPI (3DS).
     *
     * Endpoints disponibles:
     *  - /Api/spi/Auth   → Auth con 3DS (SPI)
     *  - /Api/Auth       → Auth sin 3DS
     *  - /Api/Payment    → Segundo paso SPI (envía SpiToken)
     *  - /Api/Capture    → Captura de pre-autorización
     *  - /Api/Refund     → Reembolso
     *  - /Api/Void       → Anulación
     *  - /Api/Alive      → Health check
     */
    private static function getFullUrl(string $method, bool $three_d_secure = false): string
    {
        $base = rtrim(config('services.bac.auth_url'), '/') . '/Api';

        return match ($method) {
            'auth'    => $base . ($three_d_secure ? '/spi' : '') . '/Auth',
            'payment' => $base . '/spi/Payment',  // segundo paso SPI 3DS (doc oficial)
            'capture' => $base . '/Capture',
            'refund'  => $base . '/Refund',
            'void'    => $base . '/Void',
            'alive'   => $base . '/Alive',
            default   => $base,
        };
    }

    /**
     * Convierte código de moneda alfabético a numérico ISO 4217.
     */
    private static function normalizeCurrency(string $code): string
    {
        $map = [
            'GTQ' => '320',
            'USD' => '840',
            'EUR' => '978',
        ];

        // Si ya es numérico lo devuelve tal cual
        return $map[strtoupper($code)] ?? $code;
    }

    /**
     * Envía la solicitud HTTP al gateway y devuelve la respuesta normalizada.
     */
    /**
     * Envía el SpiToken como string JSON puro al endpoint /spi/Payment.
     * Según la doc oficial, este endpoint NO requiere headers PowerTranz-PowerTranzId/Password.
     */
    private static function sendSpiPayment(string $url, string $spiToken, string $event): array
    {
        Log::info(json_encode([
            'event'   => $event,
            'type'    => 'transaction',
            'payload' => ['url' => $url, 'spi_token' => substr($spiToken, 0, 8) . '...'],
            'message' => 'Enviando SpiToken al endpoint Payment.',
        ]));

        $client = new Client();

        try {
            $response_client = $client->post($url, [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                // Body es el SpiToken como JSON string: "EL_TOKEN"
                'body' => json_encode($spiToken),
            ]);

            $statusCode = $response_client->getStatusCode();
            $body       = json_decode($response_client->getBody()->getContents(), true);

            $response = ['Code' => $statusCode, 'data' => $body];

            Log::info(json_encode([
                'event'   => $event,
                'type'    => 'transaction',
                'status'  => $statusCode === 200 ? 'success' : 'error',
                'payload' => $response,
                'message' => 'Respuesta recibida del endpoint Payment.',
            ]));

            return $response;

        } catch (Exception $e) {
            return self::serverError($e);
        }
    }

    private static function sendRequest(string $url, array $payload, string $event): array
    {
        Log::info(json_encode([
            'event'   => $event,
            'type'    => 'transaction',
            'payload' => ['url' => $url, 'body' => $payload],
            'message' => 'Enviando solicitud al servicio de pagos.',
        ]));

        $client = new Client();

        try {
            $json = 'application/json';

            $response_client = $client->post($url, [
                'headers' => [
                    'PowerTranz-PowerTranzId'       => config('services.bac.api_id'),
                    'PowerTranz-PowerTranzPassword'  => config('services.bac.api_key'),
                    'Accept'                         => $json,
                    'Content-Type'                   => $json,
                ],
                'body' => json_encode($payload),
            ]);

            $statusCode = $response_client->getStatusCode();
            $body       = json_decode($response_client->getBody()->getContents(), true);

            $response = ['Code' => $statusCode, 'data' => $body];

            Log::info(json_encode([
                'event'   => $event,
                'type'    => 'transaction',
                'status'  => $statusCode === 200 ? 'success' : 'error',
                'payload' => $response,
                'message' => 'Respuesta recibida del servicio de pagos.',
            ]));

            return $response;

        } catch (Exception $e) {
            $response = [
                'Code' => '500',
                'data' => [
                    'Message' => 'Error al conectar con el servicio de pagos.',
                    'Error'   => $e->getMessage(),
                ],
            ];

            Log::error(json_encode([
                'event'   => $event,
                'type'    => 'transaction',
                'status'  => 'error',
                'payload' => $response,
                'message' => 'Excepción al conectar con el servicio de pagos.',
            ]));

            return $response;
        }
    }

    // -------------------------------------------------------------------------
    // SIMULACIÓN (sandbox local)
    // -------------------------------------------------------------------------

    private static function simulateResponse(string $method, bool $withChallenge = false): array
    {
        if ($method === 'auth') {
            if ($withChallenge) {
                // Simula respuesta 3DS que requiere challenge (redireccion al banco)
                return [
                    'Code' => 200,
                    'data' => [
                        'TransactionType'       => 1,
                        'Approved'              => false,
                        'IsoResponseCode'       => 'SP4',   // SPI pending challenge
                        'TransactionIdentifier' => (string) Str::uuid(),
                        'SpiToken'              => 'spi-token-challenge-' . Str::random(12),
                        'ResponseMessage'       => '3DS Challenge Required',
                        'RedirectData'          => 'https://acs.bank.com/challenge?token=' . Str::random(16),
                    ],
                ];
            }

            // Simula respuesta frictionless (aprobada sin challenge)
            return [
                'Code' => 200,
                'data' => [
                    'TransactionType'       => 1,
                    'Approved'              => true,
                    'AuthorizationCode'     => strtoupper(Str::random(6)),
                    'TransactionIdentifier' => (string) Str::uuid(),
                    'TotalAmount'           => 100.0,
                    'CurrencyCode'          => '320',
                    'IsoResponseCode'       => '00',
                    'ResponseMessage'       => 'Aprobada',
                    'SpiToken'              => 'spi-token-frictionless-' . Str::random(12),
                    'BillingAddress'        => [],
                ],
            ];
        }

        if ($method === 'payment') {
            return [
                'Code' => 200,
                'data' => [
                    'TransactionType'       => 2,
                    'Approved'              => true,
                    'AuthorizationCode'     => strtoupper(Str::random(6)),
                    'TransactionIdentifier' => (string) Str::uuid(),
                    'TotalAmount'           => 100.0,
                    'CurrencyCode'          => '320',
                    'IsoResponseCode'       => '00',
                    'ResponseMessage'       => 'Aprobada',
                    'PanToken'              => Str::random(16),
                    'OrderIdentifier'       => (string) Str::uuid(),
                ],
            ];
        }

        if ($method === 'capture') {
            return [
                'Code' => 200,
                'data' => [
                    'TransactionType'           => 3,
                    'Approved'                  => true,
                    'AuthorizationCode'         => strtoupper(Str::random(6)),
                    'TransactionIdentifier'     => (string) Str::uuid(),
                    'OriginalTrxnIdentifier'    => (string) Str::uuid(),
                    'TotalAmount'               => 100.0,
                    'IsoResponseCode'           => '00',
                    'ResponseMessage'           => 'Capture successful',
                ],
            ];
        }

        if ($method === 'refund') {
            return [
                'Code' => 200,
                'data' => [
                    'TransactionType'           => 5,
                    'Approved'                  => true,
                    'AuthorizationCode'         => strtoupper(Str::random(6)),
                    'TransactionIdentifier'     => (string) Str::uuid(),
                    'OriginalTrxnIdentifier'    => (string) Str::uuid(),
                    'TotalAmount'               => 100.0,
                    'IsoResponseCode'           => '00',
                    'ResponseMessage'           => 'Refund successful',
                ],
            ];
        }

        if ($method === 'void') {
            return [
                'Code' => 200,
                'data' => [
                    'TransactionType'       => 7,
                    'Approved'              => true,
                    'TransactionIdentifier' => (string) Str::uuid(),
                    'IsoResponseCode'       => '00',
                    'ResponseMessage'       => 'Void successful',
                ],
            ];
        }

        return ['Code' => 200, 'data' => []];
    }

    // -------------------------------------------------------------------------
    // HELPERS PARA INTERPRETAR LA RESPUESTA SPI 3DS
    // -------------------------------------------------------------------------

    /**
     * Indica si la respuesta del /spi/Auth requiere que el usuario
     * complete el Challenge 3DS (redirección al banco).
     *
     * PowerTranz devuelve RedirectData con la URL del ACS cuando hay challenge.
     * El IsoResponseCode suele ser 'SP4' o 'SP6' en esos casos.
     */
    public static function requiresChallenge(array $response): bool
    {
        $data = $response['data'] ?? [];
        return !empty($data['RedirectData']) && empty($data['Approved']);
    }

    /**
     * Devuelve la URL de redirección al ACS del banco para el challenge 3DS.
     * Úsala para redirigir al usuario desde tu frontend.
     */
    public static function getChallengeUrl(array $response): ?string
    {
        return $response['data']['RedirectData'] ?? null;
    }

    /**
     * Indica si la transacción fue aprobada directamente (frictionless o sin 3DS).
     */
    public static function isApproved(array $response): bool
    {
        return ($response['data']['Approved'] ?? false) === true
            && ($response['data']['IsoResponseCode'] ?? '') === '00';
    }

    // -------------------------------------------------------------------------
    // MÉTODOS PÚBLICOS
    // -------------------------------------------------------------------------

    /**
     * PASO 1 — Auth SPI (con o sin 3DS).
     *
     * Con ThreeDSecure = false → autorización directa en un paso.
     * Con ThreeDSecure = true  → puede responder:
     *   a) Aprobado directamente (frictionless): IsoResponseCode = '00', Approved = true
     *   b) Requiere challenge:  RedirectData contiene la URL del ACS del banco.
     *      En ese caso debes redirigir al usuario a esa URL. El banco hará POST
     *      a tu MerchantResponseUrl con el SpiToken una vez que el usuario
     *      complete la autenticación.
     *
     * @param array $data {
     *   TotalAmount, CurrencyCode, ThreeDSecure,
     *   Source: { CardPan, CardCvv, CardExpiration (YYMM), CardholderName },
     *   OrderIdentifier, MerchantResponseUrl?,
     *   BillingAddress?, ShippingAddress?
     * }
     */
    public static function processAuth(array $data): array
    {
        try {
            $method = 'auth';
            $is3ds  = (bool) ($data['ThreeDSecure'] ?? false);

            $validations = [
                'TotalAmount'              => 'required|numeric|min:0.01|max:1000000000000000',
                'CurrencyCode'             => 'required|string|min:1|max:3',
                'ThreeDSecure'             => 'sometimes|boolean',

                'Source'                   => 'required|array',
                'Source.CardPan'           => 'required|string|max:19',
                'Source.CardCvv'           => 'required|string|max:4',
                'Source.CardExpiration'    => 'required|string|size:4',  // YYMM
                'Source.CardholderName'    => 'required|string|min:2|max:45',

                'OrderIdentifier'          => 'required|string|max:255',

                'BillingAddress'                    => 'sometimes|array',
                'BillingAddress.FirstName'          => 'nullable|string|max:30',
                'BillingAddress.LastName'           => 'nullable|string|max:30',
                'BillingAddress.Line1'              => 'nullable|string|max:30',
                'BillingAddress.Line2'              => 'nullable|string|max:30',
                'BillingAddress.City'               => 'nullable|string|max:25',
                'BillingAddress.State'              => 'nullable|string|max:25',
                'BillingAddress.PostalCode'         => 'nullable|string|max:10',
                'BillingAddress.CountryCode'        => 'nullable|string|max:3',
                'BillingAddress.EmailAddress'       => 'nullable|email|max:50',
                'BillingAddress.PhoneNumber'        => 'nullable|string|max:20',

                'ShippingAddress'                   => 'sometimes|array',
                'ShippingAddress.FirstName'         => 'nullable|string|max:30',
                'ShippingAddress.LastName'          => 'nullable|string|max:30',
                'ShippingAddress.Line1'             => 'nullable|string|max:30',
                'ShippingAddress.Line2'             => 'nullable|string|max:30',
                'ShippingAddress.City'              => 'nullable|string|max:25',
                'ShippingAddress.State'             => 'nullable|string|max:25',
                'ShippingAddress.PostalCode'        => 'nullable|string|max:10',
                'ShippingAddress.CountryCode'       => 'nullable|string|max:3',
                'ShippingAddress.EmailAddress'      => 'nullable|email|max:50',
                'ShippingAddress.PhoneNumber'       => 'nullable|string|max:20',
            ];

            $errors = self::validatePaymentData($data, $validations);
            if (!empty($errors)) {
                return $errors;
            }

            // --- Construir payload según doc oficial PowerTranz ---
            $payload = [
                'TransactionIdentifier' => $data['TransactionIdentifier'] ?? (string) Str::uuid(),
                'TotalAmount'           => (float) $data['TotalAmount'],
                'CurrencyCode'          => self::normalizeCurrency($data['CurrencyCode']),
                'ThreeDSecure'          => $is3ds,
                'Source'                => $data['Source'],
                'OrderIdentifier'       => $data['OrderIdentifier'],
                'AddressMatch'          => $data['AddressMatch'] ?? false,
            ];

            // MerchantResponseUrl va DENTRO de ExtendedData.ThreeDSecure (doc oficial)
            if ($is3ds) {
                $merchantUrl = $data['MerchantResponseUrl']
                    ?? config('services.bac.merchant_response_url',
                               env('BAC_MERCHANT_RESPONSE_URL', ''));

                $payload['ExtendedData'] = [
                    'ThreeDSecure' => [
                        'ChallengeWindowSize' => $data['ChallengeWindowSize'] ?? 4,
                        'ChallengeIndicator'  => $data['ChallengeIndicator']  ?? '01',
                    ],
                    'MerchantResponseUrl' => $merchantUrl,
                ];
            }

            if (!empty($data['BillingAddress'])) {
                $payload['BillingAddress'] = $data['BillingAddress'];
            }

            if (!empty($data['ShippingAddress'])) {
                $payload['ShippingAddress'] = $data['ShippingAddress'];
            }

            if (env('SIMULATE_RESPONSE', false)) {
                // Pasa true para simular challenge, false para frictionless
                return self::simulateResponse($method, $data['_simulate_challenge'] ?? false);
            }

            $url = self::getFullUrl($method, $is3ds);
            return self::sendRequest($url, $payload, $method);

        } catch (Exception $e) {
            return self::serverError($e);
        }
    }

    /**
     * PASO 2 — Payment SPI (solo para flujo 3DS con challenge).
     *
     * Este método se llama DESPUÉS de que el banco hizo POST a tu
     * MerchantResponseUrl y te envió el SpiToken en el cuerpo del request.
     *
     * PowerTranz espera recibir el SpiToken como string en el body (no en un objeto).
     *
     * Ejemplo de controller que recibe el callback del banco:
     *
     *   public function handleBacResponse(Request $request)
     *   {
     *       $spiToken = $request->input('SpiToken') ?? $request->getContent();
     *       $result   = PaymentBacService::processPayment(['SpiToken' => $spiToken]);
     *       // ... manejar resultado
     *   }
     *
     * @param array $data { SpiToken: string }
     */
    public static function processPayment(array $data): array
    {
        try {
            $method = 'payment';

            $errors = self::validatePaymentData($data, [
                'SpiToken' => 'required|string|min:1',
            ]);

            if (!empty($errors)) {
                return $errors;
            }

            if (env('SIMULATE_RESPONSE', false)) {
                return self::simulateResponse($method);
            }

            $url = self::getFullUrl($method);

            // Según doc oficial: el body es únicamente el SpiToken entre comillas.
            // El endpoint /spi/Payment NO requiere headers de autenticación PowerTranz.
            return self::sendSpiPayment($url, $data['SpiToken'], $method);

        } catch (Exception $e) {
            return self::serverError($e);
        }
    }

    /**
     * Capture — captura de una pre-autorización.
     *
     * @param array $data { TransactionIdentifier, TotalAmount }
     */
    public static function processCapture(array $data): array
    {
        try {
            $method = 'capture';

            $errors = self::validatePaymentData($data, [
                'TransactionIdentifier' => 'required|string|min:1',
                'TotalAmount'           => 'required|numeric|min:0.01',
            ]);

            if (!empty($errors)) {
                return $errors;
            }

            if (env('SIMULATE_RESPONSE', false)) {
                return self::simulateResponse($method);
            }

            $payload = [
                'TransactionIdentifier' => $data['TransactionIdentifier'],
                'TotalAmount'           => (float) $data['TotalAmount'],
            ];

            return self::sendRequest(self::getFullUrl($method), $payload, $method);

        } catch (Exception $e) {
            return self::serverError($e);
        }
    }

    /**
     * Refund — reembolso total o parcial.
     *
     * @param array $data { Refund, TransactionIdentifier?, TotalAmount, TipAmount?, TaxAmount? }
     */
    public static function processRefund(array $data): array
    {
        try {
            $method = 'refund';

            $errors = self::validatePaymentData($data, [
                'Refund'                => 'required|boolean',
                'TransactionIdentifier' => 'nullable|string',
                'TotalAmount'           => 'required|numeric|min:0.01',
                'TipAmount'             => 'sometimes|nullable|numeric|min:0',
                'TaxAmount'             => 'sometimes|nullable|numeric|min:0',
            ]);

            if (!empty($errors)) {
                return $errors;
            }

            if (env('SIMULATE_RESPONSE', false)) {
                return self::simulateResponse($method);
            }

            $payload = [
                'Refund'                => $data['Refund'],
                'TransactionIdentifier' => $data['TransactionIdentifier'] ?? null,
                'TotalAmount'           => (float) $data['TotalAmount'],
                'TipAmount'             => $data['TipAmount'] ?? null,
                'TaxAmount'             => $data['TaxAmount'] ?? null,
            ];

            return self::sendRequest(self::getFullUrl($method), $payload, $method);

        } catch (Exception $e) {
            return self::serverError($e);
        }
    }

    /**
     * Void — anulación de una transacción.
     *
     * @param array $data { TransactionIdentifier, ExternalIdentifier? }
     */
    public static function processVoid(array $data): array
    {
        try {
            $method = 'void';

            $errors = self::validatePaymentData($data, [
                'TransactionIdentifier' => 'required|string',
                'ExternalIdentifier'    => 'nullable|string',
            ]);

            if (!empty($errors)) {
                return $errors;
            }

            if (env('SIMULATE_RESPONSE', false)) {
                return self::simulateResponse($method);
            }

            $payload = [
                'TransactionIdentifier' => $data['TransactionIdentifier'],
                'ExternalIdentifier'    => $data['ExternalIdentifier'] ?? '',
            ];

            return self::sendRequest(self::getFullUrl($method), $payload, $method);

        } catch (Exception $e) {
            return self::serverError($e);
        }
    }

    /**
     * Alive — health check del gateway.
     */
    public static function processAlive(): array
    {
        try {
            $url    = self::getFullUrl('alive');
            $client = new Client();

            $res = $client->get($url, [
                'headers' => [
                    'PowerTranz-PowerTranzId'      => config('services.bac.api_id'),
                    'PowerTranz-PowerTranzPassword' => config('services.bac.api_key'),
                    'Accept'                        => 'application/json',
                ],
            ]);

            return [
                'Code' => $res->getStatusCode(),
                'data' => json_decode($res->getBody()->getContents(), true),
            ];

        } catch (Exception $e) {
            return self::serverError($e);
        }
    }

    // -------------------------------------------------------------------------
    // PRIVATE UTIL
    // -------------------------------------------------------------------------

    private static function serverError(Exception $e): array
    {
        return [
            'Code' => '500',
            'data' => [
                'Message' => 'Error al conectar con el servicio de pagos.',
                'Error'   => $e->getMessage(),
            ],
        ];
    }
}
