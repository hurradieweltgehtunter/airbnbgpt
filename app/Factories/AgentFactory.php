<?php

namespace App\Factories;

use App\Models\Agent;
use App\Agents\ImageAnalyzerAgent;
use App\Agents\WritingStyleAnalyzerAgent;
use App\Agents\HousingQuestionnaireAgent;
use App\Agents\WriterAgent;
use App\Agents\WriterAllinOneAgent;
use App\Agents\ImageDescriptionAgent;
use App\Agents\TestAgent;

use App\Services\AgentService;

class AgentFactory
{
    /**
     * Erstellt eine neue Instanz eines Agent-Modells auf Basis des Typs
     * @param Agent $agent
     */
    public static function load($agentId)
    {
        $agent = Agent::findOrFail($agentId);

        switch ($agent->name) {
            case 'ImageAnalyzerAgent':
                $newAgent = ImageAnalyzerAgent::find($agent->id);
                break;

            case 'WritingStyleAnalyzerAgent':
                $newAgent = WritingStyleAnalyzerAgent::find($agent->id);
                break;

            case 'HousingQuestionnaireAgent':
                $newAgent = HousingQuestionnaireAgent::find($agent->id);
                break;

            case 'WriterAgent':
                $newAgent = WriterAgent::find($agent->id);
                break;

            case 'WriterAllinOneAgent':
                $newAgent = WriterAllinOneAgent::find($agent->id);
                break;

            case 'ImageDescriptionAgent':
                $newAgent = ImageDescriptionAgent::find($agent->id);
                break;

            case 'TestAgent':
                $newAgent = TestAgent::find($agent->id);
                break;

            default:
                throw new ModelNotFoundException('Agent type not found in Factory: ' . $agent->name);
        }

        return $newAgent;
    }

    public static function createNew($agentName, $entity, $data = []) {
        $agentService = new AgentService();
        $availableAgent = $agentService->getAgentByName($agentName);

        if (!$availableAgent) {
            throw new ModelNotFoundException('Agent type not found in Factory: ' . $agentName);
        }

        switch ($agentName) {
            case 'ImageAnalyzerAgent':
                $newAgent = new ImageAnalyzerAgent($availableAgent->toArray());
                break;
            case 'WritingStyleAnalyzerAgent':
                $newAgent = new WritingStyleAnalyzerAgent($availableAgent->toArray());
                break;

            case 'HousingQuestionnaireAgent':
                $newAgent = new HousingQuestionnaireAgent($availableAgent->toArray());
                break;

            case 'WriterAgent':
                $newAgent = new WriterAgent($availableAgent->toArray());
                break;

            case 'WriterAllinOneAgent':
                $newAgent = new WriterAllinOneAgent($availableAgent->toArray());
                break;

            case 'WriterAllinOneAgent':
                $newAgent = new WriterAllinOneAgent($availableAgent->toArray());
                break;

            case 'ImageDescriptionAgent':
                $newAgent = new ImageDescriptionAgent($availableAgent->toArray());
                break;

            case 'TestAgent':
                $newAgent = new TestAgent($availableAgent->toArray());
                break;

            default:
                // Erstellen Sie eine Standard-Instanz eines Agent-Modells, wenn kein spezifischer Typ angegeben ist
                throw new \Exception('Agent type not found in Factory: ' . $agentName);
        }

        $newAgent->agentable()->associate($entity);

        // Map each data to the agent
        foreach ($data as $key => $value) {
            $newAgent->$key = $value;
        }

        return $newAgent;
    }
}
