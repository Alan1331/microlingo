<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\LearningUnit;
use App\Models\Level;
use App\Models\Question;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class LevelController extends Controller
{
    public function showLevelById($levelId)
    {
        $level = Level::find($levelId);

        if (!$level) {
            return response()->json(['message' => 'Level not found'], 404);
        }

        return view('admin.layouts.updateLevel', ['level' => $level, 'questions' => $level->questions]);
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

    public function updateLevel(Request $request, $levelId)
    {
        $level = Level::find($levelId);
        
        # array of vars that require validation
        $validate_vars = [
            'topic' => 'required|max:255|string',
            'content' => 'required|string',
            'videoLink' => 'required|url',
            'questionId1' => 'required|integer',
            'questionId2' => 'integer',
            'questionId3' => 'integer',
            'category1' => 'required|max:30|string',
            'category2' => 'max:30|string',
            'category3' => 'max:30|string',
        ];

        # examine type of each question to validate its input
        for($i = 1; $i <= 3; $i++) {
            $currentQuestionType = $request->input('category' . $i);
            switch ($currentQuestionType) {
                case 'Essay': // validate essay input
                    $validate_vars['editableQuestionEssay' . $i] = 'required|string';
                    $validate_vars['editableAnswer' . $i] = 'required|string';
                    break;
                case 'Multiple Choice': // validate multiple choice input
                    $validate_vars['editableQuestionMp' . $i] = 'required|string';
                    $validate_vars['choice' . $i] = 'required|string|max:1';
                    $validate_vars['customOptionInput' . $i . '-1'] = 'required|string';
                    $validate_vars['customOptionInput' . $i . '-2'] = 'required|string';
                    $validate_vars['customOptionInput' . $i . '-3'] = 'required|string';
                    break;
                default: // do nothing
                    break;
            }
        }

        # perform input validation
        $validator = Validator::make($request->all(), $validate_vars);

        # prevent update if validation was failed
        if ($validator->fails()) {
            // Get all validation error messages
            $errors = $validator->errors();

            // Retrieve all error messages as an array
            $messages = $errors->all();

            // Log the error messages
            foreach ($messages as $message) {
                Log::error('Validation error: ' . $message);
            }
            return redirect(route('units.levels.show', $level->id))
                        ->withErrors($validator)
                        ->withInput();
        }

        $validated = $validator->validated();

        # update level from validated level data
        $level->update([
            'topic' => $validated['topic'],
            'content' => $validated['content'],
            'videoLink' => $validated['videoLink'],
            'isActive' => true, # activate level
        ]);

        for($i = 1; $i <= 3; $i++) {
            if(isset($validated['category' . $i])) {
                $currentQuestionType = $validated['category' . $i];
                $currentQuestionId = intval($validated['questionId' . $i]);
    
                # get existing question object if any
                $question = Question::find($currentQuestionId);
                switch ($currentQuestionType) {
                    case 'Essay':
                        # upsert essay question
                        if(!$question) {
                            Question::create([
                                'question' => $validated['editableQuestionEssay' . $i],
                                'answer' => $validated['editableAnswer' . $i],
                                'type' => $currentQuestionType,
                                'optionA' => null,
                                'optionB' => null,
                                'optionC' => null,
                                'levelId' => $level->id,
                            ]);
                        } else {
                            $question->update([
                                'question' => $validated['editableQuestionEssay' . $i],
                                'answer' => $validated['editableAnswer' . $i],
                                'type' => $currentQuestionType,
                                'optionA' => null,
                                'optionB' => null,
                                'optionC' => null,
                            ]);
                        }
                        break;
                    case 'Multiple Choice':
                        # upsert multiple choice question
                        if(!$question) {
                            Question::create([
                                'question' => $validated['editableQuestionMp' . $i],
                                'answer' => $validated['choice' . $i],
                                'type' => $currentQuestionType,
                                'optionA' => $validated['customOptionInput' . $i . '-1'],
                                'optionB' => $validated['customOptionInput' . $i . '-2'],
                                'optionC' => $validated['customOptionInput' . $i . '-3'],
                                'levelId' => $level->id,
                            ]);
                        } else {
                            $question->update([
                                'question' => $validated['editableQuestionMp' . $i],
                                'answer' => $validated['choice' . $i],
                                'type' => $currentQuestionType,
                                'optionA' => $validated['customOptionInput' . $i . '-1'],
                                'optionB' => $validated['customOptionInput' . $i . '-2'],
                                'optionC' => $validated['customOptionInput' . $i . '-3'],
                            ]);
                        }
                        break;
                    case 'Delete':
                        # delete question if any
                        if($question) {
                            $question->delete();
                        }
                        break;
                    default: // do nothing
                        break;
                }
            }
        }

        return view('admin.layouts.updateLevel', ['level' => $level, 'questions' => $level->questions]);
    }

    public function deleteLevel($id, $levelId) {
        // Delete all videos inside the level
        $level = Level::find($levelId);
        $level->videos()->delete();

        // Delete the level
        $result = $level->delete();

        // Verify level was found
        if (!$result) {
            return redirect()->route('units.levels', $id)->with('failed', 'Failed to delete level!');
        }

        return redirect()->route('units.levels', $id)->with('success', 'Level deleted successfully!');
    }
}
