<?php

namespace App\Http\Resources;


use Carbon\Carbon;
use App\Enums\Status;
use Illuminate\Support\Str;
use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $sites = env('APP_URL');
        $path = $this->resource['img'];
        
        if( !Str::startsWith($path, "https://firebasestorage.googleapis.com") ){
            $url = Str::startsWith($path, $sites) ? str_replace($sites, '', $path) : $path;
            $path = $url;
        }

        return [
            "id" => $this->resource['id'],
            "img" => $path
        ];
    }
}
