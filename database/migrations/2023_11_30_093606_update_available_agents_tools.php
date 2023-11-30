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
        /**
         * The OpenAI API changed from using functions to using tools
         * https://platform.openai.com/docs/api-reference/chat/create#chat-create-tools
         */

        DB::table('available_agents')->truncate();

        Schema::table('available_agents', function (Blueprint $table) {
            \DB::statement("ALTER TABLE available_agents CHANGE COLUMN functions tools TEXT");
            \DB::statement("ALTER TABLE available_agents CHANGE COLUMN use_functions use_tools BOOLEAN");
            $table->string('tool_choice')->nullable(true)->description('The tool to use for the AI')->after('use_tools');
        });

        Artisan::call('db:seed', [
            '--class' => 'AvailableAgentsTableSeeder',
            '--force' => true
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the changes
        Schema::table('available_agents', function (Blueprint $table) {
            \DB::statement("ALTER TABLE available_agents CHANGE COLUMN tools functions TEXT");
            \DB::statement("ALTER TABLE available_agents CHANGE COLUMN use_tools use_functions BOOLEAN");
            $table->dropColumn('tool_choice');
        });
    }
};
