<?php

namespace App\Services;

use Exception;
use App\Models\Mark;
use App\Http\Requests\MarkRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Http\Requests\PaginateRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class MarkService
{

    protected $firebase;
    protected $pathDomain;

    protected array $filterableFields = [
        'fuelType',
        'gear',
        'hasAC'
    ];

    public $envService;

    public function __construct(FirebaseAuthService $firebase)
    {
        $this->firebase = $firebase;
        $this->pathDomain = env('APP_URL');
    }

    /**
     * Listar marcas con filtros y paginación
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

            $documents = collect($this->firebase->getAll('cars'));

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
     * Crear nueva marca
     *
     * @throws Exception
     */
    public function store(MarkRequest $request)
    {
        try {

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('marks', 'public');
            }

            return Mark::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $path ?? ""
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

            if( $request->hasFile('image') ){

                if( !empty($mark->image) ){
                    Storage::delete($mark->image);
                }

                $mark->fill($request->validated());

                $path = $request->file('image')->store('marks', 'public');
            }

            return tap($mark)->update([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $path ?? $mark->image
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
    public function show($mark)
    {
        try {
            return collect($this->firebase->getById('cars', $mark));
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
