<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentsTable extends Migration
{
    public function up()
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('description');
            $table->json('parameters')->nullable();
            $table->boolean('has_finished')->default(false);

            // Fügen Sie die polymorphe Beziehung hinzu
            $table->nullableMorphs('agentable');

            $table->timestamps();
        });

        // Bestehende 'messages' Tabelle erweitern
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->after('id');

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn('agent_id');
        });

        Schema::dropIfExists('agents');
    }
}
