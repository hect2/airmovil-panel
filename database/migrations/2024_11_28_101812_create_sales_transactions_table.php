<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('uuid');
            $table->string('id_order');
            $table->string('client_name');
            $table->string('client_uuid');
            $table->string('country')->nullable()->default('GT');
            $table->string('ip_location');
            $table->string('device_id');
            $table->string('currency')->default('GTQ');
            $table->float('total',10,2);
            $table->dateTime('date_transaction');
            $table->string('request_id');
            $table->string('request_status');
            $table->string('request_code');
            $table->string('request_auth');
            $table->string('status_transaction');
            $table->string('payment');
            $table->string('identifier_payment');
            $table->string('value_payment');
            $table->string('type_card');
            $table->string('url_voucher')->nullable();
            $table->string('url_voucher_reverse')->nullable();
            $table->string('reverse_request_id')->nullable();
            $table->dateTime('date_reverse')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_transactions');
    }
};
