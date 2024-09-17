<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
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

        return view('admin.layouts.level', ['level' => $level, 'questions' => $level->questions]);
    }

    public function showLevelForm($unitId)
    {
        $unit = LearningUnit::find($unitId);
        return view('admin.layouts.level', ['unit' => $unit]);
    }

    public function upsertLevel(Request $request, $levelId = null)
    {
        $isNewLevel = false;
        $level = null;

        # create level if not existed
        if($levelId == null) {
            $isNewLevel = true;
            $nextSortId = Level::where('unitId', $request->unitId)->max('sortId') + 1;
            $level = Level::create([
                'unitId' => $request->unitId,
                'sortId' => $nextSortId,
            ]);
        } else {
            $level = Level::find($levelId);
        }

        # perform validation
        $validator = $this->validateUpsert($request);

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

            if($isNewLevel) {
                return redirect(route('units.levels.form', $request->unitId))
                            ->withErrors($validator)
                            ->withInput();
            } else {
                return redirect(route('units.levels.show', $level->id))
                            ->withErrors($validator)
                            ->withInput();
            }
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

        if($isNewLevel) {
            return redirect(route('units.levels', $request->unitId));
        } else {
            return view('admin.layouts.level', ['level' => $level, 'questions' => $level->questions]);
        }
    }

    private function validateUpsert(Request $request) {
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
        return Validator::make($request->all(), $validate_vars);
    }

    public function deleteLevel($levelId) {
        $level = Level::find($levelId);
        $unitId = $level->learningUnit->id;
        $sortId = $level->sortId;
        $result = $level->delete();

        // Verify level was found
        if (!$result) {
            return redirect()->route('units.levels', $unitId)->with('failed', 'Failed to delete the level!');
        }

        Level::where('unitId', '=', $unitId)
                ->where('sortId', '>', $sortId)
                ->decrement('sortId', 1);

        return redirect()->route('units.levels', $unitId)->with('success', 'Level deleted successfully!');
    }
}
