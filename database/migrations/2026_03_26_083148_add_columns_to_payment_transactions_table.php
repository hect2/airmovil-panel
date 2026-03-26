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
        Schema::table('payment_transactions', function (Blueprint $table) {
            // ── Flujo 3DS ────────────────────────────────────────────────
            // Monto de la transacción flotante que debe dispararse post-3DS
            $table->decimal('total_amount_floating', 12, 2)->nullable()->default(null);

            // auth_data completo cifrado (Source + BillingAddress) para reconstruir
            // la llamada a processFloating() desde handle() sin depender del request original
            $table->text('auth_data_encrypted')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn(['total_amount_floating', 'auth_data_encrypted']);
        });
    }
};
