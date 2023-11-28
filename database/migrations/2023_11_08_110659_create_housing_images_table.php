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
        Schema::create('housing_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('housing_id');
            $table->string('path'); // Der Pfad zum Speicherort des Bildes
            $table->string('label')->default('');
            $table->string('description', 250)->default('');

            // Definiere den Fremdschlüssel zur Tabelle 'housing_rooms'
            $table->foreign('housing_id')->references('id')->on('housings')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housing_images');
    }
};
