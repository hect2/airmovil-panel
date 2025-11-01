<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Enums\Role as EnumRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\VehicleOwnerRequest;
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

}
