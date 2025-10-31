<?php

namespace App\Models\sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionFloat extends Model
{
    use HasFactory;
    protected $table = "float_transactions";
    protected $guarded = []; // Permite todos los campos

}
