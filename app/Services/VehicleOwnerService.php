<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use GuzzleHttp\Client;
use App\Enums\Role as EnumRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\VehicleOwnerRequest;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Pagination\LengthAwarePaginator;

class VehicleOwnerService
{
    public object $user;
    public array $phoneFilter = ['phone'];
    public array $roleFilter = ['role_id'];
    public array $userFilter = ['name', 'email', 'status', 'phone'];
    public array $blockRoles = [EnumRole::ADMIN];

    protected $firebase;
    protected $pathDomain;

    public function __construct(FirebaseAuthService $firebase)
    {
        $this->firebase = $firebase;
        $this->pathDomain = env('APP_URL');
    }


    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $perPage     = $request->get('per_page', 10);
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            $page        = $request->get('page', 1);

            $documents = collect($this->firebase->getAll('users'));

            // Filtrar solo los usuarios con rol ADMIN
            $documents = $documents->filter(function ($doc) {
                return isset($doc['rol']) && $doc['rol'] === 'ADMIN';
            });

            // Filtros dinámicos
            $filtered = $documents->filter(function ($doc) use ($requests) {
                foreach ($requests as $key => $value) {
                    if (in_array($key, $this->userFilter)) {

                        $key = $key == 'phone' ? 'mobile' : $key;

                        if ($key === 'except') {
                            \Log::debug("EXCEPT");
                            $exceptIds = explode('|', $value);
                            if (in_array($doc['id'], $exceptIds)) {
                                \Log::debug("IN_ARRAY");
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
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(VehicleOwnerRequest $request)
    {
        try {
            return collect($this->firebase->create('users',[
                'name'      => $request->name,
                'email'     => $request->email,
                'mobile'    => $request->phone,
                'ccode'     => $request->country_code ?? null,
                'rol'       => 'ADMIN',
                'status'    => $request->status,
                'rdate'     => now()
            ]));
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(VehicleOwnerRequest $request, $customer)
    {
        try {
            $dataUsers = [
                'name'      => $request->name,
                'email'     => $request->email,
                'mobile'    => $request->phone,
                'ccode'     => $request->country_code ?? null,
                'rol'       => 'ADMIN',
                'status'    => $request->status,
                'rdate'     => now()
            ];

            if( !empty($request->contactCode) ){
                $dataUsers['contactCode'] = $request->contactCode;
            }

            $this->firebase->update('users', $customer, $dataUsers);

            $dataUsers['id'] = $customer;

            return collect($dataUsers);
            
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show($customer)
    {
        try {
            if (!in_array(EnumRole::CUSTOMER, $this->blockRoles)) {
                return $customer;
            } else {
                throw new Exception(trans('all.message.permission_denied'), 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(User $customer)
    {
        try {
            if (!in_array(EnumRole::CUSTOMER, $this->blockRoles) && $customer->id != 2) {
                if ($customer->hasRole(EnumRole::CUSTOMER)) {
                    DB::transaction(function () use ($customer) {
                        $customer->addresses()->delete();
                        $customer->delete();
                    });
                } else {
                    throw new Exception(trans('all.message.permission_denied'), 422);
                }
            } else {
                throw new Exception(trans('all.message.permission_denied'), 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
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

            $body = [
                'mail' => $email,
                'password' => $password
            ];
    
            $loginResponse = $client->post('https://dev.universales.com/api-externas/public/login', [
                'headers' => $headers,
                'json' => $body,
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
     * @throws Exception
     */
    public function createExternalClient(VehicleOwnerRequest $VehicleOwnerRequest)
    {
        try {
            $token = $this->generateToken();

            if (empty($token)) {
                throw new Exception(trans('No se pudo autenticar con Universales'), 422);
            }

            $payload = [
                'nit'           => empty($VehicleOwnerRequest->nit) ? 'CF' : $VehicleOwnerRequest->nit,
                'nombre1'       => $VehicleOwnerRequest->name ?? '',
                'nombre2'       => '',
                'apellido1'     => $VehicleOwnerRequest->lastName ?? '',
                'apellido2'     => '',
                'tipodoc'       => $VehicleOwnerRequest->documentType ?? '',
                'doc'           => $VehicleOwnerRequest->documentId ?? '',
                'fechanac'      => !empty($VehicleOwnerRequest->birthDate)
                    ? Carbon::parse($VehicleOwnerRequest->birthDate)->format('d/m/Y')
                    : '',
                'nacionalidad'  => $VehicleOwnerRequest->country ?? 'GT',
                'actividad'     => 1,
                'celular'       => $VehicleOwnerRequest->mobile ?? '',
                'genero'        => $VehicleOwnerRequest->gender ?? '',
                'email'         => $VehicleOwnerRequest->email ?? '',
                'tipoCalle'     => 'AVE',
                'zona'          => (int) ($VehicleOwnerRequest->zone ?? 1),
                'direccion'     => $VehicleOwnerRequest->address ?? '',
                'pais'          => 'GT',
                'departamento'  => $VehicleOwnerRequest->department ?? '',
                'municipio'     => $VehicleOwnerRequest->municipality ?? '',
                'pep'           => 0,
                'tipoCliente'   => 1,
            ];

            $client = new Client();

            $response = $client->post(
                'https://dev.universales.com/api-externas/public/add-contacto',
                [
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                    ],
                    'json' => $payload,
                    'http_errors' => false,
                ]
            );

            $responseData = json_decode($response->getBody(), true);

            if( ($responseData['code'] ?? null) !== 200 || empty($responseData['recordset']['contacto']) ) {
                $rawMessage = $responseData['msg'] ?? '';

                $cleanMessage = $this->cleanOracleMessage($rawMessage);

                throw new Exception($cleanMessage, 422);
            }

            if( isset($recordset['respuesta']) && str_contains(strtolower($recordset['respuesta']), 'actualizar') ){
                throw new Exception($recordset['respuesta'], 422);
            }

            if (empty($responseData['recordset']['contacto'])) {
                throw new Exception('Contacto no generado por Universales', 422);
            }

            $recordset = $responseData['recordset'] ?? [];

            if( isset($recordset['detalle'][0]['CODIGO']) ){
                return [
                    'contacto' => $recordset['detalle'][0]['CODIGO'],
                    'message' => $recordset['respuesta']
                ];
            }

            return [
                'contacto' => $responseData['recordset']['contacto'],
                'message'  => $responseData['recordset']['respuesta'] ?? 'Contacto creado',
            ];

        } catch (RequestException $e) {
            throw new Exception(trans('Error de comunicación con Universales'), 422);
        }
    }

    private function cleanOracleMessage(string $message): string
    {

        if (preg_match('/Nit Invalido\s*!!/i', $message, $matches)) {
            return 'Nit del usuario inválido';
        }

        if (preg_match('/DPI INCORRECTO/i', $message, $matches)) {
            return 'DPI del usuario incorrecto';
        }

        if (preg_match('/ORA-20\d+:\s*(.+?)(\n|$)/', $message, $matches)) {
            return trim($matches[1]);
        }

        // Mensaje generico.
        return 'Error al crear contacto en Universales';
    }

}
