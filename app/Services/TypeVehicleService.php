<?php

namespace App\Services;

use Exception;
use App\Models\TypeVehicle;
use Illuminate\Support\Facades\Log;
use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Support\Facades\File;
use App\Http\Requests\PaginateRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\TypeVehicleRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class TypeVehicleService
{
    protected array $filterableFields = [
        'title'
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

            $filtered = $documents = collect($this->firebase->getAll('carTypes'));

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
    public function store(TypeVehicleRequest $request)
    {
        try {

            if ($request->hasFile('img')) {
                $path = $request->file('img')->store('carTypes', 'public');
            }

            return collect($this->firebase->create('carTypes',[
                'title' => $request->title,
                'img' => $this->pathDomain."/storage/".$path ?? ""
            ]));
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
    public function update(TypeVehicleRequest $request, $vehicles)
    {
        try {

            $dataImage = $this->firebase->getById('carTypes', $vehicles);
            $pathImage = str_replace(env('APP_URL').'/storage/', '', $dataImage['img']);
            Storage::disk('public')->delete($pathImage);

            if ($request->hasFile('img')) {
                $path = $request->file('img')->store('carTypes', 'public');
            }

            $this->firebase->update('carTypes', $vehicles, [
                'img' => $this->pathDomain."/storage/".$path ?? "",
                'title' => $request->title
            ]);

            return collect([
                'id' => $vehicles,
                'img' => $this->pathDomain."/storage/".$path,
                'title' => $request->title
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
    public function destroy($vehicles): void
    {
        try {
            $dataImage = $this->firebase->getById('carTypes', $vehicles);
            $pathImage = str_replace(env('APP_URL').'/storage/', '', $dataImage['img']);

            Storage::disk('public')->delete($pathImage);
            
            $this->firebase->delete('carTypes', $vehicles);
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
    public function show($vehicles): TypeVehicle
    {
        try {
            return $vehicles;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
