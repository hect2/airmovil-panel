<?php

namespace App\Models\sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransactions extends Model
{
    use HasFactory;
    protected $table = "sales_transactions_detail";
    protected $guarded = []; // Permite todos los campos

}
