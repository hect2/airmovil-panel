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
        Schema::create('sales_payment_method', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->string('payment_code');
            $table->string('type_payment');
            $table->json('credentials');
            $table->json('function');
            $table->string('merchant')->nullable();
            $table->integer('correlative');
            $table->string('currency');
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('sales_payment_method');
    }
};
