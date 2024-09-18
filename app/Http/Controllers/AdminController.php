<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserGrade;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function showDashboard()
    {
        $numOfUsers = User::count();
        $numOfCompletedLevels = UserGrade::count();

        return view('admin.index', [
            'numOfUsers' => $numOfUsers,
            'numOfCompletedLevels' => $numOfCompletedLevels,
        ]);
    }
}
