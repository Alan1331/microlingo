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

    public function showUsers()
    {
        $users = $this->user->all();

        return view('admin.layouts.kelolaPengguna', ['users' => $users]);
    }

    public function updateUser(Request $request, $noWhatsapp)
    {
        // Get all request data
        $data = $request->all();

        // Concat unit and level from request into progress variable
        $data['progress'] = $data['unit'] . "-" . $data['level'];
        unset($data['unit'], $data['level']);

        // Update the user document in Firestore
        $result = $this->user->update($noWhatsapp, $data);

        // Verify user was found
        if (!$result) {
            return redirect()->route('kelolaPengguna')->with('failed', 'Failed to update user data!');
        }

        return redirect()->route('kelolaPengguna')->with('success', 'User updated successfully!');
    }

    public function deleteUser($noWhatsapp)
    {
        // Delete the user document in Firestore
        $result = $this->user->delete($noWhatsapp);

        // Verify user was found
        if (!$result) {
            return redirect()->route('kelolaPengguna')->with('failed', 'Failed to delete user data!');
        }

        return redirect()->route('kelolaPengguna')->with('success', 'User deleted successfully!');
    }

    public function showLearningUnits()
    {
        $learningUnits = $this->learningUnit->all();

        return view('admin.layouts.materiPembelajaran', ['learningUnits' => $learningUnits]);
    }

    public function showLearningUnitById($id)
    {
        // instantiate level model
        $level = new Level($id);
        $levels = $level->all();

        return view('admin.layouts.viewLevel', ['levels' => $levels, 'unitId' => $id]);
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
            return redirect()->route('materiPembelajaran')->with('failed', 'Invalid input: id was used!');
        }

        // Create the learning unit document in Firestore
        $this->learningUnit->create([
            'id' => $request->id,
            'topic' => $request->topic,
        ]);

        // Return a status message instead of learning unit data
        return redirect()->route('materiPembelajaran')->with('success', 'Learning unit created successfully');
    }

    public function deleteUnit($id)
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
            return redirect()->route('materiPembelajaran')->with('failed', 'Failed to delete unit data!');
        }

        return redirect()->route('materiPembelajaran')->with('success', 'Unit deleted successfully!');
    }
    
    public function deleteLevel($id, $levelId) {
        try {
            App::call('App\Http\Controllers\LevelController@deleteLevel', ['unitId' => $id, 'levelId' => $levelId]);
            return redirect()->route('units.levels', $id)->with('success', 'Level deleted successfully!');
        } catch(\Exception $e) {
            Log::info("Failed to delete a level");
            return redirect()->route('units.levels', $id)->with('failed', 'Failed to delete level!');
        }
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
