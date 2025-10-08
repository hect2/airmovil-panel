<?php

namespace App\Services;

use Exception;
use App\Models\TypeVehicle;
use App\Http\Requests\TypeVehicleRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Dipokhalder\EnvEditor\EnvEditor;
use App\Http\Requests\PaginateRequest;

class TypeVehicleService
{
    protected array $filterableFields = [
        'name',
        'description',
        'image'
    ];

    public $envService;

    public function __construct(EnvEditor $envEditor)
    {
        $this->envService = $envEditor;
    }

    /**
     * Listar tipos de vehículos con filtros y paginación
     *
     * @throws Exception
     */
    public function list(PaginateRequest $request, bool $isExport = false)
    {
        try {
            $requests = $request->all();

            $method = ($request->get('paginate', 0) == 1 && !$isExport) ? 'paginate' : 'get';
            $methodValue = ($request->get('paginate', 0) == 1 && !$isExport) ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column', 'id');
            $orderType = $request->get('order_type', 'desc');

            return TypeVehicle::where(function ($query) use ($requests) {
                foreach ($requests as $key => $value) {
                    if (in_array($key, $this->filterableFields) && $value !== null && $value !== '') {
                        $query->where($key, 'like', '%' . $value . '%');
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method($methodValue);

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
            return TypeVehicle::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => 'storage/image/placeholder.png' // cambiar por manejo real de imágenes si aplica
            ]);
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
    public function update(TypeVehicleRequest $request, TypeVehicle $typeVehicle)
    {
        try {
            return tap($typeVehicle)->update([
                'name' => $request->name,
                'description' => $request->description,
                'image' => 'storage/image/placeholder.png' // actualizar lógica si se maneja subida real
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Eliminar tipo de vehículo
     *
     * @throws Exception
     */
    public function destroy(TypeVehicle $typeVehicle): void
    {
        try {
            if (File::exists($typeVehicle->image) && !$this->envService->getValue('DEMO')) {
                File::delete($typeVehicle->image);
            }

            $typeVehicle->delete();
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
    public function show(TypeVehicle $typeVehicle): TypeVehicle
    {
        try {
            return $typeVehicle;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
