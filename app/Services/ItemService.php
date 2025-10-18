<?php

namespace App\Services;


use App\Enums\Ask;
use App\Enums\Status;
use Exception;
use App\Models\Item;
use Illuminate\Support\Str;
use App\Models\ItemVariation;
use App\Http\Requests\ItemRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ChangeImageRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemService
{
    public $item;
    protected $firebase;
    protected $pathDomain;
    protected $itemFilter = [
        'name',
        'slug',
        'item_category_id',
        'price',
        'is_featured',
        'item_type',
        'tax_id',
        'status',
        'order',
        'description',
        'except'
    ];

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

            $filtered = $documents = collect($this->firebase->getAll('banners'));

            // Filtros dinámicos
            $filtered = $documents->filter(function ($doc) use ($requests) {
                foreach ($requests as $key => $value) {
                    if (in_array($key, $this->itemFilter)) {
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
            Log::info($exception->getMessage());
            throw new Exception($exception->getLine()." ".$exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(ItemRequest $request)
    {
        try {

            if ($request->hasFile('img')) {
                $path = $request->file('img')->store('items', 'public');
            }

            return collect($this->firebase->create('banners',[
                'img' => $this->pathDomain."/storage/".$path ?? ""
            ]));

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            // DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ItemRequest $request, $item)
    {
        try {
            $dataImage = $this->firebase->getById('banners', $item);
            $pathImage = str_replace(env('APP_URL').'/storage/', '', $dataImage['img']);
            Storage::disk('public')->delete($pathImage);

            if ($request->hasFile('img')) {
                $path = $request->file('img')->store('items', 'public');
            }

            $this->firebase->update('banners', $item, [
                'img' => $this->pathDomain."/storage/".$path ?? ""
            ]);

            return collect([
                'id' => $item,
                'img' => $this->pathDomain."/storage/".$path
            ]);

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            // DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy($item)
    {
        try {

            $dataImage = $this->firebase->getById('banners', $item);
            $pathImage = str_replace(env('APP_URL').'/storage/', '', $dataImage['img']);

            Storage::disk('public')->delete($pathImage);
            
            $this->firebase->delete('banners', $item);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());

            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Item $item): Item
    {
        try {
            return $item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changeImage(ChangeImageRequest $request, Item $item): Item
    {
        try {
            if ($request->image) {
                $item->clearMediaCollection('offer');
                $item->addMediaFromRequest('image')
                    ->toMediaCollection('offer', 'public_custom');

            }
            return $item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function featuredItems()
    {
        try {
            return Item::where(['is_featured' => Ask::YES, 'status' => Status::ACTIVE])->inRandomOrder()->limit(8)->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function mostPopularItems()
    {
        try {
            return Item::withCount('orders')->where(['status' => Status::ACTIVE])->orderBy('orders_count', 'desc')->limit(6)->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function itemReport(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            return Item::withCount('orders')->where(function ($query) use ($requests) {
                if (isset($requests['from_date'])  && isset($requests['to_date'])) {
                    $first_date = date('Y-m-d', strtotime($requests['from_date']));
                    $last_date  = date('Y-m-d', strtotime($requests['to_date']));
                    $query->whereDate('created_at', '>=', $first_date)->whereDate(
                        'created_at',
                        '<=',
                        $last_date
                    );
                }
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }
                }
            })->orderBy('orders_count', 'desc')->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
