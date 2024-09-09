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
}
