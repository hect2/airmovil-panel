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

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('typeVehicle', 'public');
            }

            return TypeVehicle::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $path ?? "" // cambiar por manejo real de imágenes si aplica
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
    public function update(TypeVehicleRequest $request, TypeVehicle $vehicles)
    {
        try {

            if( $request->hasFile('image') ){

                if( !empty($vehicles->image) ){
                    Storage::delete($vehicles->image);
                }

                $vehicles->fill($request->validated());

                $path = $request->file('image')->store('typeVehicle', 'public');
            }

            return tap($vehicles)->update([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $path // actualizar lógica si se maneja subida real
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
    public function destroy(TypeVehicle $vehicles): void
    {
        try {
            if (File::exists($vehicles->image) && !$this->envService->getValue('DEMO')) {
                File::delete($vehicles->image);
            }

            $vehicles->delete();
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
    public function show(TypeVehicle $vehicles): TypeVehicle
    {
        try {
            return $vehicles;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
