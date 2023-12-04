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
        Schema::create('gpt_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('enabled')->default(false);
            $table->decimal('cost_input_1k', 7, 5)->description('cost per 1000 input tokens');
            $table->decimal('cost_output_1k', 7, 5)->description('cost per 1000 output tokens');
            $table->timestamps();
        });

        // Change table available_agents field model (string) to gpt_model_id (foreign key to gpt_models)
        Schema::table('available_agents', function (Blueprint $table) {
            $table->dropColumn('model');
            $table->unsignedBigInteger('gpt_model_id')->nullable(); // Foreign Key
            $table->foreign('gpt_model_id')->references('id')->on('gpt_models');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('available_agents', function (Blueprint $table) {
            $table->dropForeign(['gpt_model_id']);
            $table->dropColumn('gpt_model_id');
            $table->string('model');
        });

        Schema::dropIfExists('gpt_models');
    }
};
