<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Housing;
use App\Models\Message;
use App\Models\HousingContent;
use App\Models\HousingRoom;
use App\Models\Agent;
use App\Models\WritingStyle;
use App\Models\WritingStyleExample;

use App\Factories\AgentFactory;

use App\Http\Resources\MessageResource;
use App\Http\Resources\HousingResource;
use App\Http\Resources\HousingIndexResource;
use App\Http\Resources\AgentResource;
use App\Http\Resources\WritingStyleIndexResource;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Custom\ChatGPT;
use App\Custom\OpenAiFunctions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Inertia\Inertia;

class HousingController extends Controller
{
    /**
     * List all housings to belonging to the authenticated user
     */
    public function index()
    {
        $housings = HousingIndexResource::collection(
            auth()
            ->user()
            ->housings()
            ->orderBy('id', 'desc')
            ->get()
        );

        return response()->json($housings);
    }

    /**
     * Show the form for creating a new resource.
     * GET /housings/create
     */
    public function create()
    {
        return Inertia::render('Housing', ['section' => 'create']);
    }

    /**
     * Display a single housing
     * GET housings/{id}
     */
    public function show(Request $request, Housing $housing)
    {
        $housing->load('rooms', 'rooms.images', 'images', 'contents', 'agents');

        return Inertia::render('Housing', ['section' => 'show', 'housing' => $housing]);
    }


    /**
     * Checks, on which step the housingis and redirects to the correct page
     */
    public function edit(Request $request, Housing $housing)
    {

        // Step 1: Address setting. This is the first step in which a new housing gets created, so we can skip the check

        // Step 2: Images. Check if the housing has already an ImageAnalyzerAgent and it is finished (has_finished = true)
        $agent = $housing->agents()->where('name', 'ImageAnalyzerAgent')->first();

        // If an ImageAnalyzerAgent is present and it is finished, redirect to the questionnaire
        if($agent && $agent->has_finished) {
            return redirect()->route('housings.showQuestionnaire', ['housing' => $housing]);
        } else {
            // If not, redirect to the images page
            return redirect()->route('housings.images', ['housing' => $housing]);
        }

        // Step 3: Questionnaire. Check if the housing has already an HousingQuestionnaireAgent and it is finished (has_finished = true)
        $agent = $housing->agents()->where('name', 'HousingQuestionnaireAgent')->first();

        // If an HousingQuestionnaireAgent is present and it is finished, redirect to the writing style selection
        if($agent && $agent->has_finished) {
            return redirect()->route('housings.writingstyleSelect', ['housing' => $housing]);
        } else {
            // If not, redirect to the questionnaire page
            return redirect()->route('housings.showQuestionnaire', ['housing' => $housing]);
        }

        // Step 4: Writing style selection. Check if the housing has already an WriterAllinOneAgent and it is finished (has_finished = true)
        $agent = $housing->agents()->where('name', 'WriterAllinOneAgent')->first();

        // If an WriterAllinOneAgent is present and it is finished, redirect to the writing page
        if($agent && $agent->has_finished) {
            return redirect()->route('housings.show', ['housing' => $housing]);
        } else {
            // If not, redirect to the writing style selection page
            return redirect()->route('housings.writingstyleSelect', ['housing' => $housing]);
        }

        return Inertia::render('Dashboard');
    }

    public function images(Request $request, Housing $housing)
    {
        $housing->load('images');

        // Check if the housing has already an ImageAnalyzerAgent and it is finished (has_finished = true)
        $agent = $housing->agents()->where('name', 'ImageAnalyzerAgent')->first();

        // If so an ImageAnalyzerAgent is present and it is finished, redirect to the questionnaire
        if($agent && $agent->has_finished) {
            return redirect()->route('housings.showQuestionnaire', ['housing' => $housing]);
        }

        return Inertia::render('Housing', ['section' => 'images', 'housing' => $housing, 'agent' => $agent ?? null]);
    }

    public function showQuestionnaire(Request $request, Housing $housing)
    {
        // We cannot start the questionnaire until images are uploaded and set. Therefore:
        // Check, if housing has an agent ImageAnalyzerAgent. If not or the Agent is not finished, redirect to /housing/id/images
        $agent = $housing->agents()->where('name', 'ImageAnalyzerAgent')->first();

        if(!$agent || !$agent->has_finished) {
            return redirect()->route('housing.images', ['housing' => $housing]);
        }

        $housing->load('rooms', 'rooms.images');

        // If no rooms are present, redirect to the /housing/id/images
        if($housing->rooms->count() == 0) {
            return redirect()->route('housing.images', ['housing' => $housing, 'agent' => $agent ?? null]);
        }

        return Inertia::render('Housing', ['section' => 'showQuestionnaire', 'housing' => $housing]);
    }

    public function writingstyleSelect(Request $request, Housing $housing)
    {

        // Check if housing already has a WriterAllinOneAgent and it is finished (has_finished = true)
        $agent = $housing->agents()->where('name', 'WriterAllinOneAgent')->first();

        // If so an WriterAllinOneAgent is present and it is finished, redirect to the questionnaire
        if($agent && $agent->has_finished) {
            return redirect()->route('housings.show', ['housing' => $housing]);
        }

        // Get all writing styles to the user
        $writingStyles = WritingStyleIndexResource::collection(auth()->user()->writingStyles()->get());

        return Inertia::render('Housing', ['section' => 'writingstyleSelect', 'housing' => $housing, 'writingStyles' => $writingStyles]);
    }

    public function write(Request $request, Housing $housing, WritingStyle $writingStyle)
    {
        // First, look if there is already an agent of this type belonging to $housing
        $agent = $housing->agents()->where('name', 'WriterAllinOneAgent')->first();

        if(!$agent) {
            // If not, create a new one
            $agent = Agent::createFromName('WriterAllinOneAgent', $housing, ['writing_style_id' => $writingStyle->id]);
        } else {
            // If so, load the specific subclass instance
            $agent = AgentFactory::load($agent->id);
        }

        return Inertia::render('Housing', ['section' => 'write', 'housing' => $housing, 'agent' => new AgentResource($agent)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'address_street'        => 'required|string|max:255',
            'address_street_number' => 'required|string|max:255',
            'address_zip'           => 'required|string|max:10',
            'address_city'          => 'required|string|max:255',
            'address_country'       => 'required|string|max:255',
            'lat'                   => 'required',
            'lng'                   => 'required',
        ]);

        $data = $request->all();

        $data['lat'] = convertToFloat($data['lat']);
        $data['lng'] = convertToFloat($data['lng']);

        $housing = Housing::createForUser(auth()->user()->id, $request->all());

        return new HousingResource($housing);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Housing $housing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Housing $housing)
    {
        // Make sure, the given housing belongs to the user
        $housing = auth()->user()->housings()->find($housing->id);

        if(!$housing) {
            // Der Raum wurde nicht gefunden oder gehört nicht dem angemeldeten Benutzer
            return response()->json(['error' => 'Housing not found or unauthorized'], 404);
        }

        $housing->delete();

        return response()->json(['success' => true]);

    }

    /**
     * Method to prepare all agents required for the writing process
     */
    public function prepare(Request $request, Housing $housing, WritingStyle $writingStyle) {
        /**
         * 1) Check if images are available
         * 2) Check if questionnaire is available and finished(?)
         * 3) Check if writing style is available
         *
         * Then:
         * 1) Generate description for each image with writingStyle
         * 2) Generate texts with writingStyle
         */

        Log::debug('HousingController@prepare: Start preparing housing ' . $housing->id . ' with writing style ' . $writingStyle->id);

        // 1) Check if images are available. If not redirect to /housing/id/images
        if($housing->images()->count() == 0) {
            Log::debug('HousingController@prepare: No images found for housing ' . $housing->id . '. Redirecting to /housing/id/images');
            return redirect()->route('housings.images', ['housing' => $housing]);
        }

        Log::debug('HousingController@prepare: Found ' . $housing->images()->count() . ' images for housing ' . $housing->id);

        // 2) Check, if housing has an Agent with name HousingQuestionnaireAgent.
        $agent = $housing->agents()->where('name', 'HousingQuestionnaireAgent')->first();

        if(!$agent || !$agent->has_finished) {
            // If not, redirect
            Log::debug('HousingController@prepare: No questionnaire found for housing ' . $housing->id . '. Redirecting to /housing/id/questionnaire');
            return redirect()->route('housings.showQuestionnaire', ['housing' => $housing]);
        }

        Log::debug('HousingController@prepare: Found finished questionnaire for housing ' . $housing->id . '.');

        // 3) Check if writing style is available and if it belongs to the auth user
        if($writingStyle->user_id != auth()->user()->id) {
            // If not, redirect
            Log::debug('HousingController@prepare: Writing style ' . $writingStyle->id . ' does not belong to user ' . auth()->user()->id . '. Redirecting to /housing/id/writingstyleSelect');
            return redirect()->route('housings.writingstyleSelect', ['housing' => $housing]);
        }

        Log::debug('HousingController@prepare: Writing style ' . $writingStyle->id . ' belongs to user ' . auth()->user()->id . '.');

        // Chekup done, create the agents
        // Check, if there are already ImageDescriptionAgents. If so, delete it
        $imageAgents = $housing->agents()->where('name', 'ImageDescriptionAgent')->get();

        foreach($imageAgents as $agent) {
            if(!$agent->has_finished)
            {
                $agent->delete();
                Log::debug('HousingController@prepare: Deleted unfinished ImageDescriptionAgent for housing ' . $housing->id);
            } else {
                Log::debug('HousingController@prepare: ImageDescriptionAgent for housing ' . $housing->id . ' is already finished. Redeirecting to show housing.');
                return redirect()->route('housings.show', ['housing' => $housing]);
            }
        }

        // Create the ImageDescriptionAgent
        $imageAgent = AgentFactory::createNew('ImageDescriptionAgent', $housing, ['parameters' => ['writing_style_id' => $writingStyle->id]]);
        $imageAgent->save();
        Log::debug('HousingController@prepare: Created ImageDescriptionAgent for housing ' . $housing->id . ' with id ' . $imageAgent->id);

        // Check, if there are already WriterAllinOneAgents. If so, delete it
        $writerAgents = $housing->agents()->where('name', 'WriterAllinOneAgent')->get();

        foreach($writerAgents as $writerAgent) {
            if(!$writerAgent->has_finished)
            {
                $writerAgent->delete();
                Log::debug('HousingController@prepare: Deleted unfinished WriterAllinOneAgent for housing ' . $housing->id);
            } else {
                Log::debug('HousingController@prepare: WriterAllinOneAgent for housing ' . $housing->id . ' is already finished. Redirecting to show housing.');
                return redirect()->route('housings.show', ['housing' => $housing]);
                break;
            }
        }

        // Create the WriterAllinOneAgent
        $writerAgent = AgentFactory::createNew('WriterAllinOneAgent', $housing, ['parameters' => ['writing_style_id' => $writingStyle->id]]);
        $writerAgent->save();
        Log::debug('HousingController@prepare: Created WriterAllinOneAgent for housing ' . $housing->id . ' with id ' . $writerAgent->id);

        // Redirect to /housing/id/run
        Log::debug('HousingController@prepare: Redirecting to /housing/' . $housing->id . '/run');
        return redirect()->route('housings.run', ['housing' => $housing]);
    }

    /**
     * Method to run the agents and create the texts
     */
    public function run (Request $request, Housing $housing) {
        Log::debug('HousingController@run: Start running housing ' . $housing->id);

        $agents = $housing->agents()->where('has_finished', false)->get();

        Log::debug('HousingController@run: Found ' . count($agents) . ' open agents for housing ' . $housing->id);
        return Inertia::render('Housing', ['section' => 'run', 'housing' => $housing, 'agents' => $agents]);
    }
}

function convertToFloat($value) {
    // Erst wird sichergestellt, dass der Wert eine Zahl ist
    $floatVal = floatval($value);

    // Dann wird die Zahl mit sprintf auf maximal 10 Stellen begrenzt, davon 7 nach dem Komma
    $formattedFloat = sprintf("%.7f", $floatVal);

    // Nun wird überprüft, ob die Gesamtlänge der Zahl nicht mehr als 10 Zeichen beträgt
    // Wenn ja, wird die Zahl gekürzt
    if (strlen(substr(strrchr($formattedFloat, "."), 1)) + strlen(substr($formattedFloat, 0, strpos($formattedFloat, '.'))) > 10) {
        $parts = explode('.', $formattedFloat);
        $parts[0] = substr($parts[0], 0, 10 - strlen($parts[1]) - 1);
        $formattedFloat = implode('.', $parts);
    }

    // Rückgabe des formatierten Floats
    return $formattedFloat;
}
