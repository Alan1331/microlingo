<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function showUsers()
    {
        $users = $this->user->all();

        return response()->json($users);
    }

    public function showUserById($noWhatsapp)
    {
        $userDocument = $this->user->find($noWhatsapp);
        if (!$userDocument) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($userDocument);
    }

    public function createUser(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'noWhatsapp' => 'required|string'
        ]);

        // Create the user document in Firestore
        $user = $this->user->create([
            'id' => $request->noWhatsapp,
            'nama' => 'unknown',
            'progress' => '1-1',
            'lokasiMenu' => 'mainMenu',
            'pekerjaan' => 'unknown',
        ]);

        // Return a status message instead of user data
        // return response()->json(['message' => 'User created successfully'], 201);
        return $user;
    }

    public function updateUser(Request $request, $noWhatsapp)
    {
        // Update the user document in Firestore
        $result = $this->user->update($noWhatsapp, $request->all());

        // Verify user was found
        if (!$result) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Return a status message
        return response()->json(['message' => 'User updated successfully'], 200);
    }

    public function deleteUser($noWhatsapp)
    {
        // Delete the user document in Firestore
        $result = $this->user->delete($noWhatsapp);

        // Verify user was found
        if (!$result) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Return a status message
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
