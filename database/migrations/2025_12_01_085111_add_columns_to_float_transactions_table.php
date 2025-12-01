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
        Schema::table('float_transactions', function (Blueprint $table) {
            $table->string('refund_id')->nullable();
            $table->dateTime('date_refund')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('float_transactions', function (Blueprint $table) {
            $table->dropColumn(['refund_id','date_refund']);
        });
    }
};
