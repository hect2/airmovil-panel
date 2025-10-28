<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable();
            $table->uuid('transaction_uuid');
            
            $table->string('original_trxn_identifier')->nullable();
            $table->string('transaction_type'); // 1=Auth, 2=Sale, 3=Capture, 4=Void, 5=Refund, 6=Credit
            $table->boolean('approved')->default(false);
            $table->string('authorization_code')->nullable();
            $table->string('transaction_identifier')->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('rrn')->nullable();
            $table->string('host_rrn')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_suffix', 4)->nullable();
            $table->string('iso_response_code')->nullable();
            $table->string('pan_token')->nullable();
            $table->string('external_identifier')->nullable();
            $table->string('order_identifier')->nullable();

            $table->text('spi_token_encrypted')->nullable();

            $table->string('refund_id')->nullable();
            $table->dateTime('date_refund')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};