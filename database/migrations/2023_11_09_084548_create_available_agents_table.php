<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvailableAgentsTable extends Migration
{
    public function up()
    {
        Schema::create('available_agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->text('system_prompt')->nullable(true)->default('');
            $table->json('initial_message')->nullable(false);
            $table->string('model')->nullable(false);
            $table->boolean('use_functions')->default(true);
            $table->json('functions')->nullable(true);
            $table->json('fake_responses')->nullable(false);
            $table->boolean('fake_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('available_agents');
    }
}
