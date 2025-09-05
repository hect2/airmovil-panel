<?php

use App\Enums\OrderStatus;

return [
    OrderStatus::PENDING          => 'Pendiente',
    OrderStatus::ACCEPT           => 'Aceptar',
    OrderStatus::PROCESSING       => 'Procesando',
    OrderStatus::OUT_FOR_DELIVERY => 'En camino',
    OrderStatus::DELIVERED        => 'Entregado',
    OrderStatus::CANCELED         => 'Anulado',
    OrderStatus::REJECTED         => 'Rechazado',
    OrderStatus::RETURNED         => 'Devuelto',


];
