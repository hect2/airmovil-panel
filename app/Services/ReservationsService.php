<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class ReservationsService
{

    protected $firebase;
    protected $pathDomain;

    protected array $filterableFields = [
        'status',
        'paymentMethodName',
        'bookedWithDriver'
    ];

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

            $documents = collect($this->firebase->getAll('reservations'));
            // \Log::info($documents);
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
     * Mostrar tipo de vehículo individual
     *
     * @throws Exception
     */
    public function show($reservation)
    {
        try {

            $reservations = $this->firebase->getById('reservations', $reservation);
            $dataFeatures = [];

            $values = array_slice($reservations['car']['features'], 0, 10);

            foreach($values as $value){
                $feature = $this->firebase->getById('carFeatures', $value);
                $dataFeatures[] = $feature['title'] ?? null;
            }

            $reservations['car']['features'] = $dataFeatures;

            return collect($reservations);

            return collect($this->firebase->getById('reservations', $reservation));
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

}
