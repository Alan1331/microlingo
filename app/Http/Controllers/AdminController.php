<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin;
use App\Models\User;
use App\Models\LearningUnit;
use App\Models\Level;

class AdminController extends Controller
{
    protected $admin;
    protected $user;
    protected $learningUnit;

    public function __construct(Admin $admin, User $user, LearningUnit $learningUnit)
    {
        $this->admin = $admin;
        $this->user = $user;
        $this->learningUnit = $learningUnit;
    }

    public function showAdmins()
    {
        $admins = $this->admin->all();

        return response()->json($admins);
    }

    public function showAdminById($email)
    {
        $adminDocument = $this->admin->find($email);
        if (!$adminDocument) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        return response()->json($adminDocument);
    }


    public function updateAdmin(Request $request, $password)
    {
        // Update the user document in Firestore
        $result = $this->admin->update($password, $request->all());

        // Verify user was found
        if (!$result) {
            return response()->json(['message' => 'Password not found'], 404);
        }

        // Return a status message
        return response()->json(['message' => 'Password updated successfully'], 200);
    }

    public function deleteAdmin($email)
    {
        // Delete the user document in Firestore
        $result = $this->admin->delete($email);

        // Verify user was found
        if (!$result) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        // Return a status message
        return response()->json(['message' => 'Email deleted successfully'], 200);
    }

    public function updateLevel(Request $request, $id, $levelId)
    {
        // instantiate level model
        $level = new Level($id);

        // Update the level document in Firestore
        $result = $level->update($levelId, $request->all());

        // Verify learning unit was found
        if (!$result) {
            return redirect()->route('units.levels', $id)->with('failed', 'Failed to update level!');
        }

        Log::info("Success update level" . $levelId . "at unit" . $id);

        return redirect()->route('units.levels', $id)->with('success', 'Level updated successfully!');
    }

    public function uploadVideo(Request $request, $id, $levelId)
    {
        // Set the maximum execution time to 300 seconds (5 minutes)
        set_time_limit(300);

        // Log the incoming request data
        Log::info('Incoming request data: ', $request->all());
        Log::info('Request files: ', $request->file());

        // Use dd() to dump and die
        dd($request->all(), $request->file());

        // upload video
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
                    $message = 'Failed to store video: ' . $video->getClientOriginalName();
                    Log::error($message);
                    return redirect()->route('units.levels', $id)->with('failed', $message);
                }
            }
            $content = $this->analyzeVideo($videoNames) . "\n";

            // instantiate level model
            $level = new Level($id);

            // Update video in the level document in Firestore
            $result = $level->update($levelId, [
                'videos' => $filePaths,
                'content' => $content,
            ]);

            // ensure video paths were added
            if (!$result) {
                $message = 'Failed to update video paths!';
                Log::info($message);
                return redirect()->route('units.levels', $id)->with('failed', $message);
            }

            $message = 'Videos were uploaded!';
            Log::info($message);
            Log::info($content);
            return redirect()->route('units.levels', $id)->with('success', $message);
            
        } else {
            $message = 'No videos detected!';
            Log::info($message);
            return redirect()->route('units.levels', $id)->with('failed', $message);
        }
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
}
