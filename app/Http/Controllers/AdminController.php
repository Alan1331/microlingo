<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function showDashboard()
    {
        $numOfUsers = User::all()->count();
        $numOfCompletedLevels = DB::table('user_grade')
                                ->select('score')
                                ->get()->count();

        return view('admin.index', [
            'numOfUsers' => $numOfUsers,
            'numOfCompletedLevels' => $numOfCompletedLevels,
        ]);
    }
}
