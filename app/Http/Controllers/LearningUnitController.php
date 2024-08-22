<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LearningUnit;
use App\Models\Level;
use Illuminate\Support\Facades\Log;

class LearningUnitController extends Controller
{
    protected $notFoundMessage;

    public function __construct()
    {
        $this->notFoundMessage = 'Learning unit not found';
    }

    public function showLearningUnits()
    {
        $learningUnits = LearningUnit::all()->sortBy('sortId');

        return view('admin.layouts.materiPembelajaran', ['learningUnits' => $learningUnits]);
    }

    public function showLearningUnitById($id)
    {
        $unit = LearningUnit::find($id);
        $levels = $unit->levels->sortBy('sortId');
        $unitNumber = $unit->sortId;

        return view('admin.layouts.viewLevel', ['levels' => $levels, 'unitId' => $id, 'unitNumber' => $unitNumber]);
    }

    public function createLearningUnit(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'topic' => 'required|string',
        ]);

        // Create the learning unit
        $unit = LearningUnit::create([
            'topic' => $request->topic,
        ]);

        if ($unit != null) {
            // Create 5 levels for the learning unit
            for ($i = 1; $i <= 5; $i++) {
                Level::create([
                    'sortId' => $i,
                    'unitId' => $unit->id,
                ]);
            }
        } else {
            return redirect()->route('materiPembelajaran')->with('failed', 'Failed to create learning unit');
        }

        // Return a status message instead of learning unit data
        return redirect()->route('materiPembelajaran')->with('success', 'Learning unit created successfully');
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

    public function deleteUnit($id)
    {
        // Delete all levels inside the unit
        $unit = LearningUnit::find($id);
        $sortId = $unit->sortId;
        $unit->levels()->delete();

        // Delete the learning unit
        $result = $unit->delete();

        // Verify learning unit was found
        if (!$result) {
            return redirect()->route('materiPembelajaran')->with('failed', 'Failed to delete unit data!');
        }

        LearningUnit::where('sortId', '>', $sortId)->decrement('sortId', 1);

        return redirect()->route('materiPembelajaran')->with('success', 'Unit deleted successfully!');
    }
}
