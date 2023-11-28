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
        Schema::create('housing_rooms', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('housing_id'); // Referenz auf die Housing ID
            $table->string('name')->nullable(false)->default(''); // Der Name des Raumes'
            $table->text('description')->nullable(false)->default(''); // Die Beschreibung des Raumes
            $table->timestamps();

            // Definiere den Fremdschlüssel zur Tabelle 'housings'
            $table->foreign('housing_id')->references('id')->on('housings')->onDelete('cascade');
        });

        Schema::table('housing_images', function (Blueprint $table) {
            $table->unsignedBigInteger('room_id')->after('id')->nullable(true);

            // Definiere den Fremdschlüssel zur Tabelle 'housing_rooms'
            $table->foreign('room_id')->references('id')->on('housing_rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housing_images', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn('room_id');
        });

        Schema::dropIfExists('housing_rooms');
    }
};
