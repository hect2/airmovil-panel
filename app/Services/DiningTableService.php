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

class DiningTableService
{
    protected array $diningTableFilter = [
        'name',
        'size',
        'branch_id',
        'status'
    ];


    public $envService;

    public function __construct(EnvEditor $envEditor)
    {
        $this->envService = $envEditor;
    }

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();

            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return CategoryCar::where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->diningTableFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            if ($key == "branch_id") {
                                $query->where($key, $request);
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
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

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('categoryCars', 'public');
            }

            return CategoryCar::create([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'status' => $request->status,
                'image' => $path ?? ""
            ]);

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(DiningTableRequest $request, CategoryCar $diningTable)
    {
        try {

            if( $request->hasFile('image') ){

                if( !empty($diningTable->image) ){
                    Storage::delete($diningTable->image);
                }

                $diningTable->fill($request->validated());

                $path = $request->file('image')->store('categoryCars', 'public');

            }

            return tap($diningTable)->update([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'status' => $request->status,
                'image' => $path ?? $diningTable->image
            ]);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(CategoryCar $diningTable): void
    {
        try {
            if(File::exists($diningTable->image) && !$this->envService->getValue('DEMO')){
                File::delete($diningTable->image);
            }
            $diningTable->delete();
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
