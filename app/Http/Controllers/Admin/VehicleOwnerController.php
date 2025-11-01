<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use App\Services\VehicleOwnerService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\VehicleOwnerRequest;
use App\Http\Resources\VehicleOwnerResource;

class VehicleOwnerController extends AdminController
{

    private VehicleOwnerService $vehicleOwnerService;

    public function __construct(VehicleOwnerService $vehicleOwnerService)
    {
        parent::__construct();
        $this->vehicleOwnerService = $vehicleOwnerService;

        $this->middleware(['permission:customers'])->only('export');
        $this->middleware(['permission:customers_create'])->only('store');
        $this->middleware(['permission:customers_edit'])->only('update');
        $this->middleware(['permission:customers_delete'])->only('destroy');
        $this->middleware(['permission:customers_show'])->only('show');
    }

    public function index(PaginateRequest $request
    ) : \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return VehicleOwnerResource::collection($this->vehicleOwnerService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(VehicleOwnerRequest $request
    ) : \Illuminate\Http\Response | VehicleOwnerResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new VehicleOwnerResource($this->vehicleOwnerService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        VehicleOwnerRequest $request,
        $customer
    ) : \Illuminate\Http\Response | VehicleOwnerResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new VehicleOwnerResource($this->vehicleOwnerService->update($request, $customer));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(User $customer
    ) : \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->vehicleOwnerService->destroy($customer);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show($customer
    ) : \Illuminate\Http\Response | VehicleOwnerResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new VehicleOwnerResource($this->vehicleOwnerService->show($customer));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
