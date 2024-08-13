<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function showUsers()
    {
        $users = User::all();

        return view('admin.layouts.kelolaPengguna', ['users' => $users]);
    }

    public function createUser(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'noWhatsapp' => 'required|string'
        ]);

        // Create new user
        User::create([
            'phoneNumber' => $request->noWhatsapp,
            'menuLocation' => 'mainMenu',
            'progress' => '1-1',
        ]);

        // Return a status message instead of user data
        return response()->json(['message' => 'User created successfully'], 201);
    }
}
