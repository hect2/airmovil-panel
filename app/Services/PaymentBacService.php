<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Validator;
use App\Helpers\PaymentLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function Laravel\Prompts\error;

class PaymentBacService
{
    /**
     * Valida los campos requeridos del pago
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
     * Valida los campos requeridos del pago
     */
    private static function getFullUrl(string $method): string
    {
        $full_url = '';
        $url = config('services.bac.auth_url') . '/Api';
        if ($method == 'auth') {
            $full_url = $url . '/Auth';
        } elseif ($method == 'capture') {
            $full_url = $url . '/Capture';
        } elseif ($method == 'refund') {
            $full_url = $url . '/Refund';
        } elseif ($method == 'payment') {
            $full_url = $url . '/Payment';
        } elseif ($method == 'void') {
            $full_url = $url . '/Void';
        } else {
            $full_url = $url;
        }

        return $full_url;
    }

    /**
     * Construye el payload a enviar
     */
    public static function buildPayload(array $data): array
    {
        $currency_code = $data['CurrencyCode'] == 'GTQ'? '320':$data['CurrencyCode'];
        $payload = [
            'TotalAmount' => $data['TotalAmount'],
            'CurrencyCode' => $currency_code,
            'ThreeDSecure' => $data['ThreeDSecure'] ?? false,
            'Source' => $data['Source'],
            'OrderIdentifier' => $data['OrderIdentifier']
        ];

        if (isset($data['BillingAddress'])) {
            $payload['BillingAddress'] = $data['BillingAddress'];
        }

        if (isset($data['ShippingAddress'])) {
            $payload['ShippingAddress'] = $data['ShippingAddress'];
        }

        return $payload;
    }

    /**
     * Simula la respuesta del endpoint /Auth
     */
    private static function simulateResponse(string $method, int $status = 200): array
    {
        $response_200 = [];
        $response_400 = [
            'Code' => 400,
            'data' => [
                'Code' => 400,
                'Message' => 'Error interno del servidor',

                'Approved' => false,
                'IsoResponseCode' => '05',
                'AuthorizationCode' => '',
                'TransactionType' => 1,
                'TransactionIdentifier' => '',
            ],
        ];
        if ($method == 'auth') {
            $response_200 = [
                'Code' => 200,
                'data' => [
                    'TransactionType' => 1,
                    'Approved' => true,
                    'AuthorizationCode' => '123456',
                    'TransactionIdentifier' => 'a4e32d19-7e18-4c9a-9e1d-9c6e08f9386e',
                    'TotalAmount' => 100.0,
                    'CurrencyCode' => 'GTQ',
                    'ResponseMessage' => 'Aprobada',
                    'SpiToken' => 'spi-token-simulacion',
                    'IsoResponseCode' => '00',
                    'BillingAddress' => [
                        'FirstName' => 'Axel',
                        'LastName' => 'Lopez',
                        'Line1' => 'Zona 10',
                        'City' => 'Guatemala',
                        'State' => 'GT',
                        'PostalCode' => '01010',
                        'CountryCode' => '320',
                        'EmailAddress' => 'axel@example.com',
                        'PhoneNumber' => '50255378432'
                    ]
                ],
            ];
        } elseif ($method == 'capture') {
            $response_200 = [
                'Code' => '200',
                'data' => [
                    'OriginalTrxnIdentifier' => 'b8a2a1f4-25c2-4c7e-88b2-8123456789ab',
                    'TransactionType' => 3,
                    'Approved' => true,
                    'AuthorizationCode' => 'AUTH1234',
                    'TransactionIdentifier' => 'c0a80101-7a89-4f1e-bc11-abcdef123456',
                    'TotalAmount' => 100.50,
                    'CurrencyCode' => '840', // USD
                    'RRN' => '987654321012',
                    'HostRRN' => '123456789012',
                    'CardBrand' => 'VISA',
                    'CardSuffix' => '1234',
                    'IsoResponseCode' => '00',
                    'EmvIssuerAuthenticationData' => null,
                    'EmvIssuerScripts' => null,
                    'EmvResponseCode' => null,
                    'ResponseMessage' => 'Capture successful',
                    'RiskManagement' => (object)[],
                    'AvsResponseCode' => 'Y',
                    'CvvResponseCode' => 'M',
                    'ThreeDSecure' => (object)[],
                    'FraudCheck' => (object)[],
                    'CustomData' => 'Simulated Capture Response',
                    'Host' => 'SimHost',
                    'PanToken' => 'tok_8d7s91js',
                    'ExternalIdentifier' => 'ext_123456789',
                    'OrderIdentifier' => 'ORD-20251019-0001',
                    'Errors' => null,
                    'RedirectData' => null,
                    'SpiToken' => 'spi_987654321',
                    'BillingAddress' => [
                        'FirstName' => 'John',
                        'LastName' => 'Doe',
                        'Line1' => '123 Main St',
                        'Line2' => 'Suite 400',
                        'City' => 'Miami',
                        'County' => 'Dade',
                        'State' => 'FL',
                        'PostalCode' => '33101',
                        'CountryCode' => '840',
                        'EmailAddress' => 'john.doe@example.com',
                        'PhoneNumber' => '+13051234567',
                    ],
                ],
            ];
        } elseif ($method == 'refund') {
            $response_200 = [
                'Code' => 200,
                'data' => [
                    'OriginalTrxnIdentifier' => '123e4567-e89b-12d3-a456-426614174000',
                    'TransactionType' => 5,
                    'Approved' => true,
                    'AuthorizationCode' => 'REF123',
                    'TransactionIdentifier' => '123e4567-e89b-12d3-a456-426614174999',
                    'TotalAmount' => 150.75,
                    'CurrencyCode' => 'USD',
                    'RRN' => '9988776655',
                    'CardBrand' => 'Visa',
                    'CardSuffix' => '1234',
                    'IsoResponseCode' => '00',
                    'ResponseMessage' => 'Refund successful',
                    'RiskManagement' => [
                        'FraudCheck' => [
                            'CustomData' => 'Safe transaction',
                            'Host' => 'PTRZ-REFUND'
                        ]
                    ],
                    'PanToken' => 'PWTOK_998877',
                    'ExternalIdentifier' => 'EXT-REFUND-001',
                    'OrderIdentifier' => 'ORDER-REF-001',
                    'BillingAddress' => [
                        'FirstName' => 'John',
                        'LastName' => 'Doe',
                        'Line1' => '123 Main St',
                        'Line2' => 'Apt 4B',
                        'City' => 'Miami',
                        'State' => 'FL',
                        'PostalCode' => '33101',
                        'CountryCode' => '840',
                        'EmailAddress' => 'john@example.com',
                        'PhoneNumber' => '15025550123'
                    ],
                    'Errors' => null
                ]
            ];
        } elseif ($method == 'payment') {
            $response_200 = [
                'Code' => 200,
                'data' => [
                    'TransactionType' => 2,
                    'Approved' => true,
                    'AuthorizationCode' => strtoupper(Str::random(6)),
                    'TransactionIdentifier' => Str::uuid(),
                    'TotalAmount' => 120.50,
                    'CurrencyCode' => 'USD',
                    'RRN' => rand(100000, 999999),
                    'IsoResponseCode' => '00',
                    'ResponseMessage' => 'Approved',
                    'PanToken' => Str::random(16),
                    'OrderIdentifier' => Str::uuid(),
                    'SpiToken' => Str::random(32),
                    'BillingAddress' => [
                        'FirstName' => 'John',
                        'LastName' => 'Doe',
                        'Line1' => '123 Main St',
                        'Line2' => 'Apt 4B',
                        'City' => 'Miami',
                        'State' => 'FL',
                        'PostalCode' => '33101',
                        'CountryCode' => '840',
                        'EmailAddress' => 'john@example.com',
                        'PhoneNumber' => '15025550123'
                    ],
                ],
            ];
        } else {
            $response_200 = [];
        }


        if ($status === 200) {
            return $response_200;
        }

        return $response_400;
    }

    private static function send_request(string $url, array $payload, string $method): array
    {
        Log::info(json_encode([
            'event' => $method,
            'type' => 'transaction',
            'payload' => ['url' => $url, 'payload' => $payload],
            'message' => 'Enviando solicitud al servicio de pagos.'
        ]));
        // Send request with Guzzle
        $client = new Client();
        try {
            $application_json = 'application/json';
            $response_client = $client->post($url, [
                'headers' => [
                    'PowerTranz-PowerTranzId' => '77701459',
                    'PowerTranz-PowerTranzPassword' => 'F5qaBzswSME1IniJhDdI1FmRRP0ByZJumTZltMBLzBpiWx4lPLOvK2',
                    'Accept' => $application_json,
                    'Content-Type' => $application_json,
                ],
                'body' => json_encode($payload),
                // 'timeout' => 30,
            ]);

            $statusCode = $response_client->getStatusCode();
            $response = [
                'Code' => $statusCode,
                'data' => json_decode($response_client->getBody()->getContents(), true),
            ];

            Log::info(json_encode([
                'event' => $method,
                'type' => 'transaction',
                'payload' => $response,
                'status' => $statusCode === 200 ? 'success' : 'error',
                'message' => 'Respuesta recibida del servicio de pagos.'
            ]));
            return $response;
        } catch (Exception $e) {
            $response = [
                'Code' => '500',
                'data' => ['Message' => 'Error al conectar con el servicio de pagos.', 'Error' => $e->getMessage()]
            ];

            Log::info(json_encode([
                'event' => $method,
                'type' => 'transaction',
                'payload' => $response,
                'status' => 'error',
                'message' => 'Error al conectar con el servicio de pagos.'
            ]));

            return $response;
        }
    }

    /**
     * Función pública para procesar el auth
     */
    public static function processAuth(array $data): array
    {
        try {
            $method = 'auth';

            // Data Validate using Validator
            $validations = [
                'TotalAmount' => 'required|numeric|min:0.01|max:1000000000000000',
                'CurrencyCode' => 'required|string|min:1|max:3',

                'Source' => 'required|array',
                'Source.CardPan' => 'required|string|max:19',
                'Source.CardCvv' => 'required|string|max:4',
                'Source.CardExpiration' => 'required|string|size:4', // YYMM
                'Source.CardholderName' => 'required|string|min:2|max:45',

                'OrderIdentifier' => 'required|string|max:255',

                // Opcionales (solo se validan si vienen)
                'BillingAddress' => 'sometimes|array',
                'BillingAddress.FirstName' => 'nullable|string|max:30',
                'BillingAddress.LastName' => 'nullable|string|max:30',
                'BillingAddress.Line1' => 'nullable|string|max:30',
                'BillingAddress.Line2' => 'nullable|string|max:30',
                'BillingAddress.City' => 'nullable|string|max:25',
                'BillingAddress.State' => 'nullable|string|max:25',
                'BillingAddress.PostalCode' => 'nullable|string|min:1|max:10',
                'BillingAddress.CountryCode' => 'nullable|string|min:1|max:3',
                'BillingAddress.EmailAddress' => 'nullable|email|max:50',
                'BillingAddress.PhoneNumber' => 'nullable|string|max:20',

                'ShippingAddress' => 'sometimes|array',
                'ShippingAddress.FirstName' => 'nullable|string|max:30',
                'ShippingAddress.LastName' => 'nullable|string|max:30',
                'ShippingAddress.Line1' => 'nullable|string|max:30',
                'ShippingAddress.Line2' => 'nullable|string|max:30',
                'ShippingAddress.City' => 'nullable|string|max:25',
                'ShippingAddress.State' => 'nullable|string|max:25',
                'ShippingAddress.PostalCode' => 'nullable|string|min:1|max:10',
                'ShippingAddress.CountryCode' => 'nullable|string|min:1|max:3',
                'ShippingAddress.EmailAddress' => 'nullable|email|max:50',
                'ShippingAddress.PhoneNumber' => 'nullable|string|max:20',
            ];

            $errors = self::validatePaymentData($data, $validations);

            if (!empty($errors)) {
                return $errors;
            }

            // Build full url
            $full_url = self::getFullUrl($method);

            // Build payload
            $payload = self::buildPayload($data);

            // Simulate response for local testing
            if (env('SIMULATE_RESPONSE', false)) {
                return self::simulateResponse($method, 200);
            }

            // Send request with Guzzle
            return self::send_request($full_url, $payload, $method);
        } catch (Exception $e) {
            return [
                'Code' => '500',
                'data' => ['Message' => 'Error al conectar con el servicio de pagos.', 'Error' => $e->getMessage()]
            ];
        }
    }

    /**
     * Función pública para procesar el capture
     */
    public static function processCapture(array $data)
    {
        try {
            $method = 'capture';

            // Data Validate using Validator
            $validations = [
                'TransactionIdentifier' => 'required|string|min:1',
                'TotalAmount' => 'required|numeric|min:0.01',
            ];

            $errors = self::validatePaymentData($data, $validations);

            if (!empty($errors)) {
                return $errors;
            }

            // Build full url
            $full_url = self::getFullUrl($method);

            // Build payload
            $payload = [
                'TransactionIdentifier' => $data['TransactionIdentifier'],
                'TotalAmount' => (float) $data['TotalAmount'],
            ];

            // Simulate response for local testing
            if (env('SIMULATE_RESPONSE', false)) {
                return self::simulateResponse($method, 200);
            }

            // Send request with Guzzle
            return self::send_request($full_url, $payload, $method);
        } catch (Exception $e) {
            return [
                'Code' => '500',
                'data' => ['Message' => 'Error al conectar con el servicio de pagos.', 'Error' => $e->getMessage()]
            ];
        }
    }

    public static function processRefund(array $data)
    {
        try {
            $method = 'refund';

            // Data Validate using Validator
            $validations = [
                'Refund' => 'required|boolean',
                'TransactionIdentifier' => 'nullable|string',
                'TotalAmount' => 'required|numeric|min:0.01',
                'TipAmount' => 'sometimes|nullable|numeric|min:0',
                'TaxAmount' => 'sometimes|nullable|numeric|min:0',
            ];

            $errors = self::validatePaymentData($data, $validations);

            if (!empty($errors)) {
                return $errors;
            }

            // Build full url
            $full_url = self::getFullUrl($method);

            // Build payload
            $payload = [
                'Refund' => $data['Refund'],
                'TransactionIdentifier' => $data['TransactionIdentifier'] ?? null,
                'TotalAmount' => $data['TotalAmount'],
                'TipAmount' => $data['TipAmount'] ?? null,
                'TaxAmount' => $data['TaxAmount'] ?? null,
            ];

            // Simulate response for local testing
            if (env('SIMULATE_RESPONSE', false)) {
                return self::simulateResponse($method, 200);
            }

            // Send request with Guzzle
            return self::send_request($full_url, $payload, $method);
        } catch (Exception $e) {
            return [
                'Code' => '500',
                'data' => ['Message' => 'Error al conectar con el servicio de pagos.', 'Error' => $e->getMessage()]
            ];
        }
    }

    public static function processVoid(array $data)
    {
        try {
            $method = 'void';

            // Data Validate using Validator
            $validations = [
                'TransactionIdentifier' => 'required|string',
                'ExternalIdentifier' => 'nullable|string',
            ];

            $errors = self::validatePaymentData($data, $validations);

            if (!empty($errors)) {
                return $errors;
            }

            // Build full url
            $full_url = self::getFullUrl($method);

            // Build payload
            $payload = [
                'TransactionIdentifier' => $data['TransactionIdentifier'],
                'ExternalIdentifier' => $data['ExternalIdentifier'] ?? '',
            ];

            // Simulate response for local testing
            if (env('SIMULATE_RESPONSE', false)) {
                return self::simulateResponse($method, 200);
            }

            // Send request with Guzzle
            return self::send_request($full_url, $payload, $method);
        } catch (Exception $e) {
            return [
                'Code' => '500',
                'data' => ['Message' => 'Error al conectar con el servicio de pagos.', 'Error' => $e->getMessage()]
            ];
        }
    }

    public static function processPayment(array $data)
    {
        $method = 'payment';

        // Data Validate using Validator
        $validations = [
            'SpiToken' => 'required|string'
        ];

        $errors = self::validatePaymentData($data, $validations);

        if (!empty($errors)) {
            return $errors;
        }

        try {
            // Build full url
            $full_url = self::getFullUrl($method);

            // Build payload
            $payload = [
                'data' => $data['SpiToken'],
            ];

            // Simulate response for local testing
            if (env('SIMULATE_RESPONSE', false)) {
                return self::simulateResponse($method, 200);
            }

            // Send request with Guzzle
            return self::send_request($full_url, $payload, $method);
        } catch (\Throwable $e) {
            return [
                'Code' => '500',
                'data' => ['Message' => 'Error al conectar con el servicio de pagos.', 'Error' => $e->getMessage()]
            ];
        }
    }
}
