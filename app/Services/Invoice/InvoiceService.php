<?php

namespace App\Services\Invoice;

use App\Models\Invoices;
use App\Models\Liquidations;
use DB;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    private Client $client;
    private string $appKey;
    private $dataEmisor = [
        'NIT' => '1514318',
        'nombreEmisor' => 'Airmovil, S.A.',
        'codigoEstablecimiento' => '1',
        'nombreComercial' => 'Airmovil, S.A.',
        'correoEmisor' => 'airmovil@airmovil.com',
        'afiliacionIVA' => 'GEN',
        'direccion' => 'Ciudad de Guatemala',
        'codigoPostal' => '4234',
        'municipio' => 'Guatemala',
        'departamento' => 'Guatemala',
        'pais' => 'GT',
    ];
    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 15,
        ]);
    }

    private function generateSatSerie(string $type = 'FACT'): string
    {
        return match ($type) {
            'FACT' => 'A',
            'NC'   => 'NC',
            'ND'   => 'ND',
            default => 'A',
        };
    }

    private function generateSatNumber(string $serie, int $length = 8): int
    {
        return DB::transaction(function () use ($serie, $length) {

            $lastNumber = Invoices::orderByDesc('id')->value('id');
            $next = $lastNumber ? ((int) $lastNumber + 1) : 1;

            return str_pad($next, $length, '0', STR_PAD_LEFT);
        });
    }

    /**
     * STEP 1: Generar XML desde JSON (FEL /api/xml)
     */
    private function generateXMLInvoice(array $data): string
    {
        $payload = [
            "dte" => [
                "datosGenerales" => [
                    "tipo" => "FACT",
                    "fechaHoraEmision" => now()->format('Y-m-d\TH:i:s'),
                    "codigoMoneda" => "GTQ"
                ],
                "emisor" => [
                    "nitEmisor" => $this->dataEmisor['NIT'],
                    "nombreEmisor" => $this->dataEmisor['nombreEmisor'],
                    "codigoEstablecimiento" => $this->dataEmisor['codigoEstablecimiento'],
                    "nombreComercial" => $this->dataEmisor['nombreComercial'],
                    "correoEmisor" => $this->dataEmisor['correoEmisor'],
                    "afiliacionIVA" => $this->dataEmisor['afiliacionIVA'],
                    "direccion" => [
                        "direccion" => $this->dataEmisor['direccion'],
                        "codigoPostal" => $this->dataEmisor['codigoPostal'],
                        "municipio" => $this->dataEmisor['municipio'],
                        "departamento" => $this->dataEmisor['departamento'],
                        "pais" => $this->dataEmisor['pais']
                    ]
                ],
                "frases" => [
                    [
                        "tipoFrase" => 1,
                        "codigoEscenario" => 1,
                    ],
                ],
                "receptor" => [
                    "idReceptor" => strtoupper(trim($data['nit'] ?? 'CF')),
                    "nombreReceptor" => $data['nombre'],
                    "correoReceptor" => $data['correo'],
                ],
                "items" => $data['items'],
                "totalImpuestos" => $data['totalImpuestos'],
                "granTotal" => $data['granTotal']
            ]
        ];

        $url_generation_xml = env('SAT_URL_GENERATION_XML');
        $app_key = env('SAT_APPKEY');
        try {
            $response = $this->client->post($url_generation_xml . '/xml?firma=true', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'appKey' => $app_key,
                ],
                'json' => $payload,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            Log::info('Respuesta FEL API XML', [
                'response' => $result
            ]);
            return $result['dte']['xml_dte'];

        } catch (RequestException $e) {
            throw new Exception(
                $e->hasResponse()
                ? $e->getResponse()->getBody()->getContents()
                : $e->getMessage()
            );
        }
    }

    /**
     * STEP 2: Obtener JSON para firma
     */
    private function signEmisorXML(string $xmlBase64): string
    {
        $url_ingestor = env('SAT_URL_INGESTOR');
        $api_key = env('SAT_API_KEY');
        try {
            $response = $this->client->post($url_ingestor . '/signature', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'apiKey' => $api_key,
                ],
                'json' => [
                    'dte' => [
                        'nit_transmitter' => $this->dataEmisor['NIT'],
                        'xml_dte' => $xmlBase64,
                    ]
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            Log::info('Respuesta FEL API signature', [
                'response' => $result
            ]);
            // El servicio retorna XML firmado (Base64 o texto según proveedor)
            return base64_decode($result['xmlSigned']);

        } catch (RequestException $e) {
            throw new Exception(
                $e->hasResponse()
                ? $e->getResponse()->getBody()->getContents()
                : $e->getMessage()
            );
        }
    }


    /**
     * STEP 3: Certificar XML con SAT
     */
    private function certificateXMLWithSAT(string $signedXml): array
    {
        $url_ingestor = env('SAT_URL_INGESTOR');
        $api_key = env('SAT_API_KEY');
        try {
            $serie = $this->generateSatSerie('FACT');
            $numero = $this->generateSatNumber($serie);

            $response = $this->client->post($url_ingestor.'/dte', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'apiKey' => $api_key,
                ],
                'json' => [
                    'dte' => [
                        'nit_transmitter' => $this->dataEmisor['NIT'],
                        "serie" => $serie,
                        "number" => $numero,
                        'xml_dte' => base64_encode($signedXml),
                    ]
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            Log::info('Respuesta FEL API certificate', [
                'response' => $result
            ]);
            // El servicio retorna XML firmado (Base64 o texto según proveedor)
            return $result;

        } catch (RequestException $e) {
            return [
                'error' => $e->hasResponse()
                    ? $e->getResponse()->getBody()->getContents()
                    : $e->getMessage()
            ];
        }
    }

    /**
     * STEP 4: Create Invoice Record
     */

    private function createInvoiceRecord(array $data, bool $isError = false): void
    {
        if (!Invoices::where('liquidation_uuid', $data['liquidation_uuid'])->exists()) {
            // Handle case where liquidation already exists
            $invoice = Invoices::create([
                'uuid' => $data['uuid'],
                'liquidation_uuid' => $data['liquidation_uuid'],
                'serie' => $data['serie'],
                'number' => $data['number'],
                'status' => $data['status'],
                'dateCert' => $data['dateCert'],
                'total' => $data['total'],
                'xmlSigned' => $data['xmlSigned'],
            ]);


            if ($isError) {
                $invoice['error'] = 1;
                $invoice['error_message'] = $data['error_message'];
                $invoice->save();
            }

            $liquidation = Liquidations::where('liquidation_uuid', $data['liquidation_uuid'])->first();
            $liquidation->invoice_uuid = $invoice->uuid;
            $liquidation->save();
        }
    }

    /**
     * FLUJO COMPLETO
     */
    public function generateInvoice(string $liquidation_uuid, array $data): void
    {
        // 1. XML
        $xml = $this->generateXMLInvoice($data);

        Log::info('XML FEL generado', [
            'xml' => $xml
        ]);

        // 2. Firma
        $signed = $this->signEmisorXML($xml);

        Log::info('XML firmado', [
            'signed' => $signed
        ]);

        // 3. Certificación
        $certified = $this->certificateXMLWithSAT($signed);

        Log::info('XML certificado', [
            'certified' => $certified
        ]);

        if (isset($certified['error'])) {
            Log::error('Error en certificación', [
                'error' => json_decode($certified['error'], true)
            ]);

            $this->createInvoiceRecord([
                'uuid' => $certified['uuid'] ?? '',
                'liquidation_uuid' => $liquidation_uuid,
                'serie' => $certified['serie'] ?? '',
                'number' => $certified['number'] ?? '',
                'status' => $certified['status'] ?? '',
                'dateCert' => $certified['dateCert'] ?? '',
                'total' => $data['granTotal'] ?? '',
                'xmlSigned' => '',
                'error_message' => $certified['error'],
            ], true);

            return;
        }

        $this->createInvoiceRecord([
            'uuid' => $certified['uuid'],
            'liquidation_uuid' => $liquidation_uuid,
            'serie' => $certified['serie'],
            'number' => $certified['number'],
            'status' => $certified['status'],
            'dateCert' => $certified['dateCert'],
            'total' => $data['granTotal'],
            'xmlSigned' => '',
        ]);
    }

    public function consultPDF(string $liquidation_uuid, int $format = 1): string
    {
        $url_pdf = env('SAT_PDF_URL');
        $invoice = Invoices::where('liquidation_uuid', $liquidation_uuid)->first();
        if (!$invoice) {
            return '';
        }

        $pdf = $url_pdf . '?uuid=' . $invoice->uuid . '&format=' . $format;
        Log::error('PDF URL', [
            'pdf_url' => $pdf
        ]);
        return $pdf;
    }

}
