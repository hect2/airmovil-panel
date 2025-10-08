<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\TypeVehicle;
use App\Exports\TypeVehiclesExport;
use App\Services\TypeVehicleService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\TypeVehicleRequest;
use App\Http\Resources\TypeVehicleResource;
use Maatwebsite\Excel\Facades\Excel;

class TypesVehiclesController extends AdminController
{
    private TypeVehicleService $typeVehicleService;

    public function __construct(TypeVehicleService $typeVehicleService)
    {
        parent::__construct();
        $this->typeVehicleService = $typeVehicleService;

        $this->middleware(['permission:types-of-cars'])->only('export');
        $this->middleware(['permission:types_of_cars_create'])->only('store');
        $this->middleware(['permission:types_of_cars_edit'])->only('update');
        $this->middleware(['permission:types_of_cars_delete'])->only('destroy');
        $this->middleware(['permission:types_of_cars_show'])->only('show');
    }

    public function index(PaginateRequest $request)
    {
        try {
            return TypeVehicleResource::collection($this->typeVehicleService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(TypeVehicleRequest $request)
    {
        try {
            return new TypeVehicleResource($this->typeVehicleService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(TypeVehicle $typeVehicle)
    {
        try {
            return new TypeVehicleResource($this->typeVehicleService->show($typeVehicle));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(TypeVehicleRequest $request, TypeVehicle $typeVehicle)
    {
        try {
            return new TypeVehicleResource($this->typeVehicleService->update($request, $typeVehicle));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(TypeVehicle $typeVehicle)
    {
        try {
            $this->typeVehicleService->destroy($typeVehicle);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request)
    {
        try {
            return Excel::download(new TypeVehiclesExport($this->typeVehicleService, $request), 'TypesVehicles.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
