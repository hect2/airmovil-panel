<?php

namespace App\Services;

use App\Http\Requests\PaginateRequest;
use App\Http\Requests\MarkRequest;
use App\Models\Mark;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Dipokhalder\EnvEditor\EnvEditor;

class MarkService
{
    protected array $markFilter = [
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
     * Listar marcas con filtros y paginación
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

            return Mark::where(function ($query) use ($requests) {
                foreach ($requests as $key => $value) {
                    if (in_array($key, $this->markFilter) && $value !== null && $value !== '') {
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
     * Crear nueva marca
     *
     * @throws Exception
     */
    public function store(MarkRequest $request)
    {
        try {
            return Mark::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => 'storage/image/pruebas.png' // imagen dummy por defecto
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Actualizar marca
     *
     * @throws Exception
     */
    public function update(MarkRequest $request, Mark $mark)
    {
        try {
            return tap($mark)->update([
                'name' => $request->name,
                'description' => $request->description,
                'image' => 'storage/image/pruebas.png' // cambiar si soportas carga real
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Eliminar marca
     *
     * @throws Exception
     */
    public function destroy(Mark $mark): void
    {
        try {
            if (File::exists($mark->image) && !$this->envService->getValue('DEMO')) {
                File::delete($mark->image);
            }
            $mark->delete();
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Mostrar marca individual
     *
     * @throws Exception
     */
    public function show(Mark $mark): Mark
    {
        try {
            return $mark;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
