<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class MarkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $geoPoint = $this['pickLocation']['geoPoint'] ?? null;

        return [
            'id' => $this['id'],
            'pickAddress' => $this['pickAddress'] ?? null,
            'minimumBookingHours' => $this['minimumBookingHours'] ?? null,
            'engineHp' => $this['engineHp'] ?? null,
            'priceType' => $this['priceType'] ?? null,
            'rating' => $this['rating'] ?? null,
            'description' => $this['description'] ?? null,
            'plate' => $this['plate'] ?? null,
            'urlImages' => $this['urlImages'] ?? [],
            'totalKM' => $this['totalKM'] ?? null,
            'title' => $this['title'] ?? null,
            'type' => [
                'id' => $this['type']['id'] ?? null,
                'title' => $this['type']['title'] ?? null,
                'img' => $this['type']['img'] ?? null,
            ],
            'driverRentPrice' => $this['driverRentPrice'] ?? null,
            'totalSeats' => $this['totalSeats'] ?? null,
            'features' => $this['features'] ?? [],
            'fuelType' => $this['fuelType'] ?? null,
            'hasAC' => $this['hasAC'] ?? false,
            'rentPrice' => $this['rentPrice'] ?? null,
            'brand' => [
                'id' => $this['brand']['id'] ?? null,
                'title' => $this['brand']['title'] ?? null,
                'img' => $this['brand']['img'] ?? null,
            ],
            'gear' => $this['gear'] ?? null,
            'urlCover' => $this['urlCover'] ?? null,
            'user' => [
                'id' => $this['user']['id'] ?? null,
                'name' => $this['user']['name'] ?? null,
                'email' => $this['user']['email'] ?? null,
                'mobile' => $this['user']['mobile'] ?? null,
                'ccode' => $this['user']['ccode'] ?? null,
                'rol' => $this['user']['rol'] ?? null,
                'wallet' => $this['user']['wallet'] ?? null,
                'status' => $this['user']['status'] ?? null,
                'profile_pic' => $this['user']['profile_pic'] ?? null,
                'rdate' => $this['user']['rdate'] ?? null,
            ],
            'publishStatus' => $this['publishStatus'] ?? null,
            'pickLocation' => [
                'geoHash' => $this['pickLocation']['geoHash'] ?? null,
                'geoPoint' => $geoPoint ? [
                    'latitude' => method_exists($geoPoint, 'latitude') ? $geoPoint->latitude() : null,
                    'longitude' => method_exists($geoPoint, 'longitude') ? $geoPoint->longitude() : null,
                ] : [
                    'latitude' => null,
                    'longitude' => null,
                ],
            ],
            'status' => $this['status'] ?? null,
        ];
    }

    private function parseGeoPoint($geoPoint)
    {
        if (is_object($geoPoint) && method_exists($geoPoint, 'latitude')) {
            return [
                'latitude' => $geoPoint->latitude(),
                'longitude' => $geoPoint->longitude(),
            ];
        }

        if (is_array($geoPoint)) {
            return [
                'latitude' => $geoPoint['latitude'] ?? null,
                'longitude' => $geoPoint['longitude'] ?? null,
            ];
        }

        return ['latitude' => null, 'longitude' => null];
    }
}
