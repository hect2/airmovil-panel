<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class DiningTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            "id"             => $this->id,
            "name"           => $this->resource->name,
            "slug"           => $this->resource->slug ?? '',
            "category"           => $this->resource->category,
            // "qr_code"        => asset($this->qr_code),
            // "branch_id"      => $this->branch_id,
            // "branch_name"    => optional($this->branch)->name,
            "status"         => (integer)$this->resource->status,
            // "qr"             => $this->qr,
            // "branch_address" => $this->branch->address,
            "description"   => $this->resource->description,
        ];
    }
}