<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeVehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $sites = env('APP_URL');
        $path = $this->resource['img'];
        
        if( !Str::startsWith($path, "https://firebasestorage.googleapis.com") ){
            $url = Str::startsWith($path, $sites) ? str_replace($sites, '', $path) : $path;
            $path = $url;
        }

        return [
            "id" => $this->resource['id'],
            "title" => $this->resource['title'],
            "img" => $path
        ];
    }
}
