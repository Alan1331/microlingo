<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LearningUnit;
use App\Models\Level;
use Illuminate\Support\Facades\Log;

class LearningUnitController extends Controller
{
    protected $learningUnit;
    protected $notFoundMessage;

    public function __construct(LearningUnit $learningUnit)
    {
        $this->learningUnit = $learningUnit;
        $this->notFoundMessage = 'Learning unit not found';
    }

    public function showLearningUnits()
    {
        $learningUnits = $this->learningUnit->all();

        return response()->json($learningUnits);
    }

    public function showLearningUnitById($id)
    {
        $learingUnitDocument = $this->learningUnit->find($id);
        if (!$learingUnitDocument) {
            return response()->json(['message' => $this->notFoundMessage], 404);
        }

        return response()->json($learingUnitDocument);
    }

    public function createLearningUnit(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'id' => 'required|integer',
            'topic' => 'required|string',
        ]);

        // Ensure the unique of id
        $learingUnitDocument = $this->learningUnit->find($request->id);
        if ($learingUnitDocument) {
            return response()->json(['message' => 'Invalid input: id was used'], 403);
        }

        // Create the learning unit document in Firestore
        $this->learningUnit->create([
            'id' => $request->id,
            'topic' => $request->topic,
        ]);

        // Return a status message instead of learning unit data
        return response()->json(['message' => 'Learning unit created successfully'], 201);
    }

    public function updateLearningUnit(Request $request, $id)
    {
        // Update the learning unit document in Firestore
        $result = $this->learningUnit->update($id, $request->all());

        // Verify learning unit was found
        if (!$result) {
            return response()->json(['message' => $this->notFoundMessage], 404);
        }

        // Return a status message
        return response()->json(['message' => 'Learning unit updated successfully'], 200);
    }

    public function deleteLearningUnit($id)
    {
        // Delete all levels inside the unit
        // instantiate level model
        $level = new Level($id);
        $levels = $level->all();

        foreach($levels as $level) {
            try {
                app()->call([LevelController::class, 'deleteLevel'], ['unitId' => $id, 'levelId' => $level['id']]);
            } catch(\Exception $e) {
                Log::info("Failed to delete a level");
            }
        }

        // Delete the learning unit document in Firestore
        $result = $this->learningUnit->delete($id);

        // Verify learning unit was found
        if (!$result) {
            return response()->json(['message' => $this->notFoundMessage], 404);
        }

        // Return a status message
        return response()->json(['message' => 'Learning unit deleted successfully'], 200);
    }
}
