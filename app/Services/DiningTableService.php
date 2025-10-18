<?php

namespace App\Services;


use Exception;
use App\Models\Branch;
use App\Models\CategoryCar;
use App\Models\DiningTable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Support\Facades\File;
use App\Http\Requests\PaginateRequest;
use Illuminate\Support\Facades\Storage;
use Smartisan\Settings\Facades\Settings;
use App\Http\Requests\DiningTableRequest;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Pagination\LengthAwarePaginator;

class DiningTableService
{
    protected array $diningTableFilter = [
        'title'
    ];


    protected $firebase;
    protected $pathDomain;
    public $envService;

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

            $filtered = $documents = collect($this->firebase->getAll('carBrands'));

            // Filtros dinámicos
            $filtered = $documents->filter(function ($doc) use ($requests) {
                foreach ($requests as $key => $value) {
                    if (in_array($key, $this->diningTableFilter)) {
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

            // return CategoryCar::where(function ($query) use ($requests) {
            //     foreach ($requests as $key => $request) {
            //         if (in_array($key, $this->diningTableFilter)) {
            //             if ($key == "except") {
            //                 $explodes = explode('|', $request);
            //                 if (count($explodes)) {
            //                     foreach ($explodes as $explode) {
            //                         $query->where('id', '!=', $explode);
            //                     }
            //                 }
            //             } else {
            //                 if ($key == "branch_id") {
            //                     $query->where($key, $request);
            //                 } else {
            //                     $query->where($key, 'like', '%' . $request . '%');
            //                 }
            //             }
            //         }
            //     }
            // })->orderBy($orderColumn, $orderType)->$method(
            //     $methodValue
            // );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(DiningTableRequest $request)
    {
        try {

            if ($request->hasFile('img')) {
                $path = $request->file('img')->store('carBrands', 'public');
            }

            return collect($this->firebase->create('carBrands',[
                'title' => $request->title,
                'img' => $this->pathDomain."/storage/".$path ?? ""
            ]));

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(DiningTableRequest $request, $diningTable)
    {
        try {

            $dataImage = $this->firebase->getById('carBrands', $diningTable);
            $pathImage = str_replace(env('APP_URL').'/storage/', '', $dataImage['img']);
            Storage::disk('public')->delete($pathImage);

            if ($request->hasFile('img')) {
                $path = $request->file('img')->store('carBrands', 'public');
            }

            $this->firebase->update('carBrands', $diningTable, [
                'img' => $this->pathDomain."/storage/".$path ?? "",
                'title' => $request->title
            ]);

            return collect([
                'id' => $diningTable,
                'img' => $this->pathDomain."/storage/".$path,
                'title' => $request->title
            ]);
            
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy($diningTable): void
    {
        try {
            $dataImage = $this->firebase->getById('carBrands', $diningTable);
            $pathImage = str_replace(env('APP_URL').'/storage/', '', $dataImage['img']);

            Storage::disk('public')->delete($pathImage);
            
            $this->firebase->delete('carBrands', $diningTable);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(CategoryCar $diningTable): CategoryCar
    {
        try {
            return $diningTable;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
