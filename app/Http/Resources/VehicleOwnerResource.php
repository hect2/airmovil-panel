<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleOwnerResource extends JsonResource
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
            "id"           => $this['id'],
            "name"         => $this['name'],
            "username"     => $this['username'] ?? null,
            "email"        => $this['email'],
            "branch_id"    => $this['branch_id'] ?? null,
            "phone"        => $this['mobile'] === null ? '' : $this['mobile'],
            "status"       => $this['status'],
            "image"        => $this['image'] ?? null,
            "country_code" => $this['ccode'],
            "nit"          => $this['nit'] ?? null,
            "lastName"     => $this['lastName'] ?? null,
            "documentType"      => $this['documentType'] ?? null,
            "documentId"   => $this['documentId'] ?? null,
            "birthDate"    => $this['birthDate'] ?? null,
            "country"      => $this['country'] ?? null,
            "mobile"       => $this['mobile'] ?? null,
            "gender"       => $this['gender'] ?? null,
            "zone"         => $this['zone'] ?? null,
            "address"      => $this['address'] ?? null,
            "department"   => $this['department'] ?? null,
            "municipality" => $this['municipality'] ?? null,
            "contactCode"  => $this['contactCode'] ?? null,
            "messages"     => ""
        ];
    }
}
