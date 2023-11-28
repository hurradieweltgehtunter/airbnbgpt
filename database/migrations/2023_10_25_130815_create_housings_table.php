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
        Schema::create('housings', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary Key
            $table->unsignedBigInteger('belongs_to'); // Foreign Key zu 'users'
            $table->string('name')->nullable(true);
            $table->timestamps();
            $table->foreign('belongs_to')->references('id')->on('users');

            $table->string('address_street_number')->default('');
            $table->string('address_street')->default('');
            $table->string('address_city')->default('');
            $table->string('address_zip')->default('');
            $table->string('address_country')->default('');
            $table->string('address_sublocality')->nullable(true);
            $table->string('address_administrative_area_level_1')->nullable(true)->description('Bundesland');
            $table->string('address_sublocality_level_1')->nullable(true)->description('Stadtteil');

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housings');
    }
};
