<?php

namespace App\Models\sales;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\sales\PaymentTransactions;

class Transactions extends Model
{
    use HasFactory;
    protected $table = "sales_transactions";
    protected $guarded = []; // Permite todos los campos

    public function detail()
    {
        return $this->hasMany('App\Models\sales\DetailTransactions', 'transaction_uuid', 'uuid');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function captures()
    {
        return $this->hasMany(PaymentTransactions::class, 'transaction_uuid', 'uuid')->whereNull('refund_id');
    }

    public function error()
    {
        return $this->hasOne('App\Models\sales\ResponseCode', 'code', 'request_code')
            ->select('description','code','message')
            ->where('language', '=','ES');
    }
}
