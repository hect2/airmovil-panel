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
        Schema::create('liquidations', function (Blueprint $table) {
            $table->id();
            $table->string('liquidation_uuid')->unique();
            $table->string('transaction_uuid')->unique();
            $table->string('invoice_uuid')->nullable();
            $table->decimal('porcentage_commission', 12, 2);
            $table->decimal('amount_commission', 12, 2);
            $table->decimal('transaction_total', 12, 2);
            $table->decimal('total', 12, 2);
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
        Schema::dropIfExists('liquidations');
    }
};
