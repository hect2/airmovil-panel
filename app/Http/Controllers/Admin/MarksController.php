<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Mark; // Modelo para marcas
use App\Exports\MarksExport;
use App\Services\MarkService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\MarkRequest;
use App\Http\Resources\MarkResource;
use Maatwebsite\Excel\Facades\Excel;

class MarksController extends AdminController
{
    private MarkService $markService;

    public function __construct(MarkService $markService)
    // public function __construct(protected FirestoreService $firestore) 
    {
        parent::__construct();
        $this->markService = $markService;

        $this->middleware(['permission:marksCars'])->only('export');
        $this->middleware(['permission:marksCars_create'])->only('store');
        $this->middleware(['permission:marksCars_edit'])->only('update');
        $this->middleware(['permission:marksCars_delete'])->only('destroy');
        $this->middleware(['permission:marksCars_show'])->only('show');
    }
//? composer require google/cloud-firestore
    // public function getAll()
    // {
    //     return $this->firestore->getAll('cardBrands');
    // }

    // public function getActiveOnly()
    // {
    //     return collect($this->getAll())->where('active', true)->values();
    // }

    public function index(PaginateRequest $request)
    {
        try {
            return MarkResource::collection($this->markService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(MarkRequest $request)
    {
        try {
            return new MarkResource($this->markService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Mark $mark): \Illuminate\Http\Response | MarkResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new MarkResource($this->markService->show($mark));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(MarkRequest $request, Mark $mark)
    {
        try {
            return new MarkResource($this->markService->update($request, $mark));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Mark $mark)
    {
        try {
            $this->markService->destroy($mark);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request)
    {
        try {
            return Excel::download(new MarksExport($this->markService, $request), 'Marks.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
