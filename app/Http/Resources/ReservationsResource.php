<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $geoPoint = $this['car']['pickLocation']['geoPoint'] ?? null;
        return [
            'id'                    => $this['id'],
            'transactionId'         => $this['transactionId'] ?? null,
            'createdAt'             => $this['createdAt'] ?? null,
            'status'                => $this['status'] ?? null,
            'cancleReason'          => $this['cancleReason'] ?? null,

            // Fechas y tiempos
            'pickupDate'            => $this['pickupDate'] ?? null,
            'pickupTime'            => $this['pickupTime'] ?? null,
            'returnDate'            => $this['returnDate'] ?? null,
            'returnTime'            => $this['returnTime'] ?? null,

            // Pagos
            'paymentMethodName'     => $this['paymentMethodName'] ?? null,
            'subtotal'              => $this['subtotal'] ?? 0,
            'totalAmount'           => $this['totalAmount'] ?? 0,
            'taxAmount'             => $this['taxAmount'] ?? 0,
            'taxPercentage'         => $this['taxPercentage'] ?? 0,
            'walletAmount'          => $this['walletAmount'] ?? 0,
            'couponAmount'          => $this['couponAmount'] ?? 0,

            // Configuración de reserva
            'totalDayOrHr'          => $this['totalDayOrHr'] ?? 0,
            'bookedWithDriver'      => $this['bookedWithDriver'] ?? false,
            'driverDeliveryAtCarLocation' => $this['driverDeliveryAtCarLocation'] ?? false,

            // Coordenadas de entrega personalizada
            'customDeliveryLocation' => $this->parseGeoPoint($this['customDeliveryLocation'] ?? null),

            // Documentos e imágenes
            'customerDocuments'         => $this['customerDocuments'] ?? [],
            'reservationDocumentsImages'=> $this['reservationDocumentsImages'] ?? [],
            'preReservationImages'      => $this['preReservationImages'] ?? [],

            // Información del usuario que hace la reserva
            'user' => [
                'id'                => $this['user']['id'] ?? null,
                'name'              => $this['user']['name'] ?? null,
                'email'             => $this['user']['email'] ?? null,
                'mobile'            => $this['user']['mobile'] ?? null,
                'ccode'             => $this['user']['ccode'] ?? null,
                'rol'               => $this['user']['rol'] ?? null,
                'wallet'            => $this['user']['wallet'] ?? null,
                'status'            => $this['user']['status'] ?? null,
                'profile_pic'       => $this['user']['profile_pic'] ?? null,
                'rdate'             => $this['user']['rdate'] ?? null,
                'backgroundCheckUrl'=> $this['user']['backgroundCheckUrl'] ?? null,
                'dpiUrl'            => $this['user']['dpiUrl'] ?? null,
            ],

            // Información del vehículo
            'car' => [
                'id'            => $this['car']['id'] ?? null,
                'title'         => $this['car']['title'] ?? null,
                'plate'         => $this['car']['plate'] ?? null,
                'description'   => $this['car']['description'] ?? null,
                'gear'          => $this['car']['gear'] ?? null,
                'engineHp'      => $this['car']['engineHp'] ?? null,
                'fuelType'      => $this['car']['fuelType'] ?? null,
                'rentPrice'     => $this['car']['rentPrice'] ?? null,
                'driverRentPrice'=> $this['car']['driverRentPrice'] ?? null,
                'totalSeats'    => $this['car']['totalSeats'] ?? null,
                'totalKM'       => $this['car']['totalKM'] ?? null,
                'rating'        => $this['car']['rating'] ?? null,
                'priceType'     => $this['car']['priceType'] ?? null,
                'hasAC'         => $this['car']['hasAC'] ?? false,
                'status'        => $this['car']['status'] ?? null,
                'pickAddress'   => $this['car']['pickAddress'] ?? null,
                'publishStatus' => $this['car']['publishStatus'] ?? null,
                'driverName'    => $this['car']['driverName'] ?? null,
                'driverPhone'   => $this['car']['driverPhone'] ?? null,
                'urlCover'      => $this['car']['urlCover'] ?? null,
                'urlImages'     => $this['car']['urlImages'] ?? [],
                'propertyDocumentUrl' => $this['car']['propertyDocumentUrl'] ?? null,

                'pickLocation' => [
                    'geoHash' => $this['car']['pickLocation']['geoHash'] ?? null,
                    'geoPoint' => $geoPoint ? [
                        'latitude' => method_exists($geoPoint, 'latitude') ? $geoPoint->latitude() : null,
                        'longitude' => method_exists($geoPoint, 'longitude') ? $geoPoint->longitude() : null,
                    ] : [
                        'latitude' => null,
                        'longitude' => null,
                    ],
                ],

                'features' => $this['car']['features'] ?? [],

                'brand' => [
                    'id'    => $this['car']['brand']['id'] ?? null,
                    'title' => $this['car']['brand']['title'] ?? null,
                    'img'   => $this['car']['brand']['img'] ?? null,
                ],

                'type' => [
                    'id'    => $this['car']['type']['id'] ?? null,
                    'title' => $this['car']['type']['title'] ?? null,
                    'img'   => $this['car']['type']['img'] ?? null,
                ],

                'user' => [
                    'id'      => $this['car']['user']['id'] ?? null,
                    'name'    => $this['car']['user']['name'] ?? null,
                    'email'   => $this['car']['user']['email'] ?? null,
                    'mobile'  => $this['car']['user']['mobile'] ?? null,
                    'rol'     => $this['car']['user']['rol'] ?? null,
                    'wallet'  => $this['car']['user']['wallet'] ?? null,
                    'status'  => $this['car']['user']['status'] ?? null,
                ],
            ],
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
