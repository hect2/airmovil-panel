<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PolizesRequest;
use App\Http\Requests\PaginateRequest;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Pagination\LengthAwarePaginator;

class PolizesService
{

    protected array $filterableFields = [
        'policyNumber',
        'nit'
    ];

    protected array $filterableFieldsUser = [
        'role'
    ];

    protected $firebase;
    protected $pathDomain;

    public function __construct(FirebaseAuthService $firebase)
    {
        $this->firebase = $firebase;
        $this->pathDomain = env('APP_URL');
    }

    /**
     * Listar tipos de vehículos con filtros y paginación
     *
     * @throws Exception
     */
    public function list(PaginateRequest $request, bool $isExport = false)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $perPage     = $request->get('per_page', 10);
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            $page        = $request->get('page', 1);

            $filtered = $documents = collect($this->firebase->getAll('insurancePolicies'));

            // Filtros dinámicos
            $filtered = $documents->filter(function ($doc) use ($requests) {
                foreach ($requests as $key => $value) {
                    if (in_array($key, $this->filterableFields)) {
                        if ($key === 'except') {
                            $exceptIds = explode('|', $value);
                            if (in_array($doc['id'], $exceptIds)) {
                                return false;
                            }
                        } elseif ($key === 'item_category_id') {
                            if (!isset($doc[$key]) || $doc[$key] != $value) {
                                return false;
                            }
                        } else {
                            if (!isset($doc[$key]) || stripos($doc[$key], $value) === false) {
                                return false;
                            }
                        }
                    }
                }
                return true;
            });

            $sorted = $filtered->sortBy([
            [$orderColumn, $orderType === 'desc' ? SORT_DESC : SORT_ASC],
        ])->values();

        if ( $method === 'paginate' ) {
            $total = $sorted->count();
            $items = $sorted->forPage($page, $perPage)->values();

            return new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                [
                    'path' => request()->url(), // base URL
                    'query' => request()->query(), // mantiene los parámetros en los links
                ]
            );
        }

        return $sorted;

        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Crear un nuevo tipo de vehículo
     *
     * @throws Exception
     */
    public function store(PolizesRequest $request)
    {
        try {

            $tokenAuth = $this->generateToken();

            if(empty($tokenAuth)){
                throw new Exception("Ha ocurrido un error 1", 422);
            }

            // $this->validateNit($request->nit, $tokenAuth);

            $partsPolize = explode('-', $request->policyNumber);

            return collect($this->firebase->create('insurancePolicies', [
                    'policyNumber' => $request->policyNumber,
                    'nit' => $request->nit,
                    "customerId" => $request->customerId,
                    "isActive" => (boolean)$request->status,
                    "startDate" => Carbon::parse($request->startDate),
                    "endDate" => Carbon::parse($request->endDate),
                    "insuranceUser" => env('INSUR_USER'),
                    "paymentMethod" => env('PAYMENT_METHOD'),
                    "type" => env('TYPE'),
                    "contractorNumber" => "12123123",
                    "encryptedPolicyNumber" => "POLICE-ENCRYPT",
                    "ramo" => $partsPolize[0],
                    "subRamo" => $partsPolize[1],
                    "office" => $partsPolize[2],
                    "solicitude" => $partsPolize[3],
                    "module" => $partsPolize[4]
                ]
            ));
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Actualizar tipo de vehículo
     *
     * @throws Exception
     */
    public function update(PolizesRequest $request, $polize)
    {
        try {

            $tokenAuth = $this->generateToken();

            if(empty($tokenAuth)){
                throw new Exception("Ha ocurrido un error 1", 422);
            }

            $code = $this->validateNit($request->nit, $tokenAuth);
            \Log::info($code);
            $partsPolize = explode('-', $request->policyNumber);

            $this->firebase->update('insurancePolicies', $polize, [
                'policyNumber' => $request->policyNumber,
                'nit' => $request->nit,
                "customerId" => $request->customerId,
                "isActive" => (boolean)$request->status,
                "startDate" => Carbon::parse($request->startDate),
                "endDate" => Carbon::parse($request->endDate),
                "insuranceUser" => env('INSUR_USER'),
                "paymentMethod" => env('PAYMENT_METHOD'),
                "type" => env('TYPE'),
                "contractorNumber" => $code,
                "encryptedPolicyNumber" => "POLICE-ENCRYPT",
                "ramo" => $partsPolize[0],
                "subRamo" => $partsPolize[1],
                "office" => $partsPolize[2],
                "solicitude" => $partsPolize[3],
                "module" => $partsPolize[4]
            ]);

            return collect([
                'id' => $polize,
                'policyNumber' => $request->policyNumber,
                'nit' => $request->nit,
                "customerId" => $request->customerId,
                "isActive" => (boolean)$request->status,
                "startDate" => Carbon::parse($request->startDate),
                "endDate" => Carbon::parse($request->endDate),
                "insuranceUser" => env('INSUR_USER'),
                "paymentMethod" => env('PAYMENT_METHOD'),
                "type" => env('TYPE'),
                "contractorNumber" => $code,
                "encryptedPolicyNumber" => "POLICE-ENCRYPT",
                "ramo" => $partsPolize[0],
                "subRamo" => $partsPolize[1],
                "office" => $partsPolize[2],
                "solicitude" => $partsPolize[3],
                "module" => $partsPolize[4]
            ]);
            
        } catch (Exception $exception) {
            Log::error($exception->getLine()." ".$exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Eliminar tipo de vehículo
     *
     * @throws Exception
     */
    public function destroy($polize): void
    {
        try {
            $this->firebase->delete('insurancePolicies', $polize);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Mostrar tipo de vehículo individual
     *
     * @throws Exception
     */
    public function show($polize)
    {
        try {
            return $polize;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     *  Autenticacion
     * 
     * @throws Exception
     */
    public function generateToken(): string
    {

        try {
            
            $client = new Client();
            $email = env('EMAIL_UNIVERSALES');
            $password = env('PASSWORD_UNIVERSALES');

            $headers = [
                'Content-Type' => 'application/json',
            ];

            $body = json_encode([
                'mail' => $email,
                'password' => $password
            ]);
    
            $loginResponse = $client->post('https://dev.universales.com/api-externas/public/login', [
                'headers' => $headers,
                'body' => $body,
                'http_errors' => false,
            ]);
    
            $loginData = json_decode($loginResponse->getBody(), true);

            if (
                !isset($loginData['code']) || 
                $loginData['code'] != 200 ||
                empty($loginData['recordset']['JWToken'])
            ) {
                return "";
            }
    
            $token = $loginData['recordset']['JWToken'];
            
            return $token; 
        } catch (RequestException $exception) {
            return "";
        }


    }

    /**
     * Validar Nit
     * 
     * @throws Exception
     */
    public function validateNit($nit, $token)
    {
        try {

            $client = new Client();

            $nitResponse = $client->get("https://dev.universales.com/api-externas/public/contacto/{$nit}", [
                'headers' => [
                    'Authorization' => "Bearer {$token}"
                ],
                'http_errors' => false,
            ]);

            $data = json_decode($nitResponse->getBody(), true);
            \Log::info($data);
            if (
                isset($data['code']) && $data['code'] != 200 &&
                isset($data['recordset']['descripcion']) &&
                strtolower($data['recordset']['descripcion']) !== 'Activo'
            ) {
                throw new Exception("El nit ingresado no es valido", 422);
            }

            return $data['recordset']['codigo'];


        } catch (RequestException $exception) {
            throw new Exception("Ha ocurrido un error al conectarse a universales", 422);
        }
    }

    /**
     * Encriptar poliza
     * 
     * @throws Exception
     */
    public function encryptPolize($polize, $token): void
    {
        try {

            $client = new Client(['timeout' => 5]);

            $responseEncrypt = $client->get("https://dev.universales.com/api-endosos-web/encrypt/{$polize}", [
                'headers' => [
                    'Authorization' => "Bearer {$token}"
                ],
                'http_errors' => false,
            ]);

            $data = json_decode($responseEncrypt->getBody(), true);

            // if (
            //     isset($data['code']) && $data['code'] != 200 &&
            //     isset($data['recordset']['descripcion']) &&
            //     strtolower($data['recordset']['descripcion']) !== 'activo'
            // ) {
            //     throw new Exception("El nit ingresado no es valido", 422);
            // }


        } catch (RequestException $exception) {
            throw new Exception("Ha ocurrido un error al conectarse a universales", 422);
        }
    }

    /**
     * Listar tipos de vehículos con filtros y paginación
     *
     * @throws Exception
     */
    public function listUserAdmin(PaginateRequest $request, bool $isExport = false)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $perPage     = $request->get('per_page', 10);
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            $page        = $request->get('page', 1);

            $filtered = $documents = collect($this->firebase->getAll('users'));

            // Filtros dinámicos
            $filtered = $documents->filter(function ($doc) use ($requests) {
                foreach ($requests as $key => $value) {
                    if (in_array($key, $this->filterableFieldsUser)) {
                        if ($key === 'except') {
                            $exceptIds = explode('|', $value);
                            if (in_array($doc['id'], $exceptIds)) {
                                return false;
                            }
                        } elseif ($key === 'item_category_id') {
                            if (!isset($doc[$key]) || $doc[$key] != $value) {
                                return false;
                            }
                        } else {
                            if (!isset($doc[$key]) || stripos($doc[$key], $value) === false) {
                                return false;
                            }
                        }
                    }
                }
                return true;
            });

            $sorted = $filtered->sortBy([
            [$orderColumn, $orderType === 'desc' ? SORT_DESC : SORT_ASC],
        ])->values();

        // if ( $method === 'paginate' ) {
        //     $total = $sorted->count();
        //     $items = $sorted->forPage($page, $perPage)->values();

        //     return new LengthAwarePaginator(
        //         $items,
        //         $total,
        //         $perPage,
        //         $page,
        //         [
        //             'path' => request()->url(), // base URL
        //             'query' => request()->query(), // mantiene los parámetros en los links
        //         ]
        //     );
        // }

        return $sorted;

        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

//     public function listUserAdmin(PaginateRequest $request)
// {
//     try {
//         $requests    = $request->all();
//         $orderColumn = $request->get('order_column') ?? 'id';
//         $orderType   = $request->get('order_type') ?? 'desc';

//         $documents = collect($this->firebase->getAll('users'));

//         $filtered = $documents->filter(function ($doc) {
//             return isset($doc['role']) && strtoupper($doc['role']) === 'ADMIN';
//         });

//         $filtered = $filtered->filter(function ($doc) use ($requests) {
//             foreach ($requests as $key => $value) {
//                 if (in_array($key, $this->filterableFieldsUser)) {
//                     if ($key === 'except') {
//                         $exceptIds = explode('|', $value);
//                         if (in_array($doc['id'], $exceptIds)) {
//                             return false;
//                         }
//                     } else {
//                         if (!isset($doc[$key]) || stripos((string) $doc[$key], (string) $value) === false) {
//                             return false;
//                         }
//                     }
//                 }
//             }
//             return true;
//         });

//         $sorted = $filtered->sortBy([
//             [$orderColumn, $orderType === 'desc' ? SORT_DESC : SORT_ASC],
//         ])->values();
//             \Log::info($sorted->values());
//         return $sorted->values();

//     } catch (Exception $exception) {
//         Log::error($exception->getMessage());
//         throw new Exception($exception->getMessage(), 422);
//     }
// }


}
