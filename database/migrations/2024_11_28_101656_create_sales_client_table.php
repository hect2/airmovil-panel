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
        Schema::create('sales_client', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('uuid');
            $table->string('device_id');
            $table->string('name');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('code_phone')->nullable()->default('502');
            $table->string('email');
            $table->string('city')->nullable()->default('Guatemama');
            $table->string('state')->nullable()->default('Guatemama');
            $table->string('country')->nullable()->default('GT');
            $table->string('postal_code')->nullable()->default('01010');
            $table->string('location')->nullable()->default('Guatemala');
            $table->string('type_identifier')->nullable()->default('NIT');
            $table->string('identifier');
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
        Schema::dropIfExists('sales_client');
    }
};
