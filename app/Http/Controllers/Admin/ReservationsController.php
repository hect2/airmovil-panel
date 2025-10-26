<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ReservationsService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ReservationsRequest;
use App\Http\Resources\ReservationsResource;

class ReservationsController extends Controller
{

    private ReservationsService $reservationsService;

    public function __construct(ReservationsService $reservationsService)
    {
        // parent::__construct();
        $this->reservationsService = $reservationsService;

        // $this->middleware(['permission:reservations'])->only('export');
        // $this->middleware(['permission:reservations_create'])->only('store');
        // $this->middleware(['permission:reservations_edit'])->only('update');
        // $this->middleware(['permission:reservations_delete'])->only('destroy');
        // $this->middleware(['permission:reservations_show'])->only('show');
    }

    public function index(PaginateRequest $request)
    {
        try {
            return ReservationsResource::collection($this->reservationsService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(ReservationsRequest $request)
    {
        try {
            return new ReservationsResource($this->reservationsService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show($reservation)
    {
        try {
            return new ReservationsResource($this->reservationsService->show($reservation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(ReservationsRequest $request, $reservation)
    {
        try {
            return new ReservationsResource($this->reservationsService->update($request, $reservation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy($reservation)
    {
        try {
            $this->reservationsService->destroy($reservation);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
