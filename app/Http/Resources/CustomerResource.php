<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            "id"           => $this['id'],
            "name"         => $this['name'],
            "username"     => $this['username'] ?? null,
            "email"        => $this['email'],
            "branch_id"    => $this['branch_id'] ?? null,
            "phone"        => $this['mobile'] === null ? '' : $this['mobile'],
            "status"       => $this['status'],
            "image"        => $this['image'] ?? null,
            "country_code" => $this['ccode'],
            "messages"     => ""
        ];
    }
}
