<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\LearningUnit;
use App\Models\Level;
use Illuminate\Support\Facades\Http;

class LevelController extends Controller
{
    public function showLevels($unitId)
    {
        // instantiate level model
        $level = new Level($unitId);
        $levels = $level->all();

        return response()->json($levels);
    }

    public function showLevelById($unitId, $levelId)
    {
        // instantiate level model
        $level = new Level($unitId);
        $levelDocument = $level->find($levelId);

        if (!$levelDocument) {
            return response()->json(['message' => 'Level not found'], 404);
        }

        return response()->json($levelDocument);
    }

    public function createLevel($unitId, Request $request)
    {
        // Set the maximum execution time to 300 seconds (5 minutes)
        set_time_limit(300);

        // instantiate level model
        $level = new Level($unitId);

        // Define validation rules
        $rules = [
            'id' => 'required|integer',
            'topic' => 'required|string',
            'videos.*' => 'required|file|mimes:mp4,avi,mov|max:102400', // Validate each element in the videos array
        ];

        // Custom error messages (optional)
        $messages = [
            'id.required' => 'id is required.',
            'id.integer' => 'id should be integer.',
            'topic.required' => 'topic is required.',
            'topic.string' => 'topic should be string.',
            'videos.*.required' => 'Each video file is required.',
            'videos.*.mimes' => 'Each video file must be a file of type: mp4, avi, mov.',
            'videos.*.max' => 'Each video file must not be greater than 100MB.',
        ];

        // Validate the incoming request
        $request->validate($rules, $messages);

        if(isset($request->question) || isset($request->correctAnswer)) {
            $request->validate([
                'question' => 'required|string',
                'correctAnswer' => 'required|string'
            ]);
        }

        // Ensure the unique of id
        $levelDocument = $level->find($request->id);
        if ($levelDocument) {
            return response()->json(['message' => 'Invalid input: id was used'], 403);
        }

        // Handle the files
        $content = '';
        $filePaths = [];
        if ($request->hasFile('videos')) {
            Log::info('Video is detected');
            $videoNames = array();
            foreach ($request->file('videos') as $video) {
                // Generate a unique filename
                $uniqueFileName = Str::uuid() . '.' . $video->getClientOriginalExtension();

                // Save the video to the storage
                $path = $video->storeAs('public/videos', $uniqueFileName);
                $path = trim($path, 'public/');
                if($path) {
                    Log::info('Video stored at: ' . $path);
                    $filePaths[] = $path;
                    $videoNames[] = $uniqueFileName;
                } else {
                    Log::error('Failed to store video: ' . $video->getClientOriginalName());
                }
            }
            $content = $this->analyzeVideo($videoNames) . "\n";
        } else {
            Log::info('Video is not detected');
        }

        // Create the level document in Firestore
        $levelDoc = $level->create([
            'id' => $request->input('id'),
            'topic' => $request->input('topic'),
            'videos' => $filePaths,
            'content' => $content,
        ]);

        // Return a status message instead of level data
        return response()->json(['message' => 'Level created successfully'], 201);
    }

    public function analyzeVideo($videoNames)
    {
        // Set the maximum execution time to 300 seconds (5 minutes)
        set_time_limit(300);
        // Call the Flask API
        $response = Http::timeout(300)->post(env('VIDEO_ANALYZER_ENDPOINT'), [
            'video_names' => $videoNames,
        ]);

        if ($response->failed()) {
            Log::info("Failed analyzing the video");
            return "Content not available due to analyze video failure";
        }

        return $response['message'];
    }

    public function updateLevel(Request $request, $unitId, $levelId)
    {
        // instantiate level model
        $level = new Level($unitId);

        // Update the level document in Firestore
        $result = $level->update($levelId, $request->all());

        // Verify learning unit was found
        if (!$result) {
            return response()->json(['message' => 'Level not found'], 404);
        }

        // Return a status message
        return response()->json(['message' => 'Level updated successfully'], 200);
    }

    public function deleteLevel($unitId, $levelId)
    {
        // instantiate level model
        $level = new Level($unitId);

        // Find the level document by ID
        $levelDocument = $level->find($levelId);
        if (!$levelDocument) {
            return response()->json(['message' => 'Level not found'], 404);
        }

        // Check if the videos attribute exists and is not empty
        if (!isset($levelDocument['videos']) || empty($levelDocument['videos'])) {
            Log::info('No videos found for this level.');
        }

        // Delete the associated video files from storage
        $videoPaths = $levelDocument['videos'];
        Log::info('Video paths: ' . json_encode($videoPaths));
        
        foreach ($videoPaths as $path) {
            $path = 'public/' . $path;
            Log::info('Deleting video at: ' . $path);
            if (Storage::exists($path)) {
                Storage::delete($path);
                Log::info('Deleted video at: ' . $path);
            } else {
                Log::warning('Video not found at: ' . $path);
            }
        }

        // Delete the level document from Firestore
        $level->delete($levelId);

        // Return a status message
        return response()->json(['message' => 'Level deleted successfully'], 200);
    }
}
