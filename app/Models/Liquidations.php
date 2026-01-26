<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Liquidations extends Model
{
    use HasFactory;

    protected $table = "liquidations";
    protected $guarded = []; // Permite todos los campos
}
