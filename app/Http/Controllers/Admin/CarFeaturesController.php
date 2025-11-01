<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CarFeaturesService;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\CarFeaturesResource;

class CarFeaturesController extends AdminController
{

    private CarFeaturesService $carFeaturesService;

    public function __construct(CarFeaturesService $carFeaturesService)
    {
        parent::__construct();
        $this->carFeaturesService = $carFeaturesService;

        $this->middleware(['permission:car_feature'])->only('export');
        $this->middleware(['permission:car_feature_create'])->only('store');
        $this->middleware(['permission:car_feature_edit'])->only('update');
        $this->middleware(['permission:car_feature_delete'])->only('destroy');
        $this->middleware(['permission:car_feature_show'])->only('show');
    }

    public function index(PaginateRequest $request)
    {
        try {
            return CarFeaturesResource::collection($this->carFeaturesService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
