<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GptModelsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('gpt_models')->insert([
            [
                'id'                => 1,
                'name'              => 'gpt-3.5-turbo',
                'enabled'           => true,
                'cost_input_1k'     => 0.0001,
                'cost_output_1k'    => 0.0001,
            ],
            [
                'id'                => 2,
                'name'              => 'gpt-4-vision-preview',
                'enabled'           => true,
                'cost_input_1k'     => 0.01,
                'cost_output_1k'    => 0.03,
            ],
            [
                'id'                => 3,
                'name'              => 'gpt-4',
                'enabled'           => true,
                'cost_input_1k'     => 0.03,
                'cost_output_1k'    => 0.06,
            ],
            [
                'id'                => 4,
                'name'              => 'gpt-4-1106-preview',
                'enabled'           => true,
                'cost_input_1k'     => 0.01,
                'cost_output_1k'    => 0.03,
            ],
        ]);
    }
}
