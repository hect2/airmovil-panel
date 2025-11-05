<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use App\Services\PolizesService;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PolizesRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\PolizesResource;

class PolizesController extends AdminController
{

    private PolizesService $polizeService;

    public function __construct(PolizesService $polizeService)
    {
        parent::__construct();
        $this->polizeService = $polizeService;

        $this->middleware(['permission:polizes'])->only('export');
        $this->middleware(['permission:polizes_create'])->only('store');
        $this->middleware(['permission:polizes_edit'])->only('update');
        $this->middleware(['permission:polizes_delete'])->only('destroy');
        $this->middleware(['permission:polizes_show'])->only('show');
        $this->middleware(['permission:polizes_getUserAdmin'])->only('getUsersAdmin');
    }

    public function index(PaginateRequest $request)
    {
        try {
            return PolizesResource::collection($this->polizeService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show($polize)
    {
        try {
            return new PolizesResource($this->polizeService->show($polize));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(PolizesRequest $request)
    {
        try {
            return new PolizesResource($this->polizeService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(PolizesRequest $request, $polize)
    {
        try {
            return new PolizesResource($this->polizeService->update($request, $polize));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy($polize)
    {
        try {
            $this->polizeService->destroy($polize);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function getUsersAdmin(PaginateRequest $request)
    {
        try {
            return response()->json($this->polizeService->listUserAdmin($request), 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request)
    {
        try {
            return Excel::download(new TypeVehiclesExport($this->polizeService, $request), 'Polizes.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

}
