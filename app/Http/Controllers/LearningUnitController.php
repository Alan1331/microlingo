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

        // Calculate average grade for each level
        foreach ($levels as $level) {
            $level->averageGrade = round($level->users()->avg('score'));
        }

        return view('admin.layouts.viewLevel', ['levels' => $levels, 'unitId' => $id, 'unitNumber' => $unitNumber]);
    }

    public function createLearningUnit(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'topic' => 'required|string',
        ]);

        // Retrieve the highest unit sortId to assign the next sortId
        $highestSortId = LearningUnit::max('sortId');
        $nextSortId = $highestSortId + 1;

        // Create the learning unit
        $unit = LearningUnit::create([
            'topic' => $request->topic,
            'sortId' => $nextSortId,
        ]);

        if ($unit == null) {
            return redirect()->route('materiPembelajaran')->with('failed', 'Failed to create learning unit');
        }

        // Return a status message instead of learning unit data
        return redirect()->route('materiPembelajaran')->with('success', 'Learning unit created successfully');
    }

    public function updateLearningUnit(Request $request, $id)
    {
        $unit = LearningUnit::find($id);
        // Check if the learning unit exists
        if (!$unit) {
            return redirect()->route('materiPembelajaran')->with('failed', 'Learning unit not found');
        }
        
        // Update the learning unit
        $result = $unit->update($request->all());

        // Verify learning unit was found
        if (!$result) {
            return redirect()->route('materiPembelajaran')->with('failed', 'Failed to update the topic');
        }

        // Return a status message
        return redirect()->route('materiPembelajaran')->with('success', 'The topic was successfully updated');
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
