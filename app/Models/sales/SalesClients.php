<?php

namespace App\Models\sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesClients extends Model
{
    use HasFactory;
    protected $table = "sales_client";
    protected $guarded = []; // Permite todos los campos

}
