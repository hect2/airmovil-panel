<?php

namespace App\Models\sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransactions extends Model
{
    use HasFactory;
    protected $table = "payment_transactions";
    protected $guarded = []; // Permite todos los campos

}
