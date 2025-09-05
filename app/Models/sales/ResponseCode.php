<?php

namespace App\Models\sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseCode extends Model
{
    use HasFactory;
    protected $table = "sales_code_error";
    protected $guarded = []; // Permite todos los campos
}
