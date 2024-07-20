<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Admin;

class AdminController extends Controller
{
    protected $admin;

    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
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
}
