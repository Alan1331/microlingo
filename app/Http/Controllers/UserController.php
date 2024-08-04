<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    protected $user;
    protected $notFoundMessage;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->notFoundMessage = 'User not found';
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
            return response()->json(['message' => $this->notFoundMessage], 404);
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
        $this->user->create([
            'id' => $request->noWhatsapp,
            'nama' => 'unknown',
            'progress' => '1-1',
            'lokasiMenu' => 'mainMenu',
            'pekerjaan' => 'unknown',
            'currentQuestion' => 'unset',
        ]);

        // Return a status message instead of user data
        return response()->json(['message' => 'User created successfully'], 201);
    }

    public function updateUser(Request $request, $noWhatsapp)
    {
        // Update the user document in Firestore
        $result = $this->user->update($noWhatsapp, $request->all());

        // Verify user was found
        if (!$result) {
            return response()->json(['message' => $this->notFoundMessage], 404);
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
            return response()->json(['message' => $this->notFoundMessage], 404);
        }

        // Return a status message
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
