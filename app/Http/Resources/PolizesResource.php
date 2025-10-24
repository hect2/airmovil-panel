<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PolizesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->resource['id'],
            "contractorNumber" => $this->resource['contractorNumber'] ?? "",
            "customerId" => $this->resource['customerId'] ?? "",
            "encryptedPolicyNumber" => $this->resource['encryptedPolicyNumber'] ?? "",
            "insuranceUser" => $this->resource['insuranceUser'] ?? "",
            "isActive" => $this->resource['isActive'] ?? "",
            "module" => $this->resource['module'] ?? "",
            "nit" => $this->resource['nit'] ?? "",
            "office" => $this->resource['office'] ?? "",
            "paymentMethod" => $this->resource['paymentMethod'] ?? "",
            "policyNumber" => $this->resource['policyNumber'] ?? "",
            "ramo" => $this->resource['ramo'] ?? "",
            "solicitude" => $this->resource['solicitude'] ?? "",
            "endDate" => $this->resource['endDate'] ?? "",
            "startDate" => $this->resource['startDate'] ?? "",
            "subRamo" => $this->resource['subRamo'] ?? "",
            "type" => $this->resource['type'] ?? ""
        ];
    }
}
