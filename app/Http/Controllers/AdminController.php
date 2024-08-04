<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        return view('admin.layouts.viewLevel', ['levels' => $levels]);
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
}
