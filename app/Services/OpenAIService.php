<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class OpenAIService {
    private $fake_enabled;

    /**
     * @return mixed
     */
    public function __construct(bool $fakeEnabled = false)
    {
        $this->fake_enabled = $fakeEnabled;
    }

    /**
     * Method to run a specific AI Request
     *
     * @param array $params
     * @return mixed
     */
    public function runAI(array $params)
    {
        try {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/AIRequest.log'),
            ])->info(print_r($params, true));

            if($this->fake_enabled == true) {

                $fakeResponse = $this->getFakeResponse();

                OpenAI::fake([
                    CreateResponse::fake($fakeResponse),
                ]);

                sleep(3);
            }

            $AIResponse = OpenAI::chat()->create($params);

            Log::debug('Received AI message for agent: ' . get_class($this));
            Log::debug((array) $AIResponse);

            return $AIResponse;

        // Catch a timeout error
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::debug('Timeout in OpenAI API request: ' . $e->getMessage());
            Log::debug((array) $params);
            throw $e;
        } catch (\Exception $e) {
            Log::debug('Error in OpenAI API request: ' . $e->getMessage());
            Log::debug((array) $params);
            throw $e;
        }
    }
}
