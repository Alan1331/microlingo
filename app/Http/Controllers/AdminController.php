<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\User;

class AdminController extends Controller
{
    protected $admin;
    protected $user;

    public function __construct(Admin $admin, User $user)
    {
        $this->admin = $admin;
        $this->user = $user;
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
}
