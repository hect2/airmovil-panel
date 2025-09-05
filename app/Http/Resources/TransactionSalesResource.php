<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionSalesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $statusMap = [
            'ACCEPT' => 'Aceptada',
            'REVERSE' => 'Anulada',
            'REJECT' => 'Rechazada',
        ];

        return [
            'id'                    => $this->id,
            'uuid'                  => $this->uuid,
            'order_id'              => $this->id_order,
            'order_serial_no'       =>  $this->id_order,
            'client_name'           => $this->client_name,
            'request_id'            => $this->request_id,
            'amount'                => $this->total,
            'payment'               => strtoupper($this->payment),
            'type'                  => $this->type_card,
            'sign'                  => $this->currency,
            'status_transaction'    => $statusMap[$this->status_transaction] ?? $this->status_transaction, // Traducción
            'date'                  => AppLibrary::datetime($this->date_transaction)
        ];
    }
}
