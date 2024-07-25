<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FirebaseLoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckFirebaseRole;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/whatsapp/send', 'App\Http\Controllers\WhatsAppController@sendMessage');
Route::post('/whatsapp/receive', 'App\Http\Controllers\WhatsAppController@receiveMessage');

Route::get('/users', 'App\Http\Controllers\UserController@showUsers');
Route::get('/users/{noWhatsapp}', 'App\Http\Controllers\UserController@showUserById');
Route::post('/users', 'App\Http\Controllers\UserController@createUser');
Route::put('/users/{noWhatsapp}', 'App\Http\Controllers\UserController@updateUser');
Route::delete('/users/{noWhatsapp}', 'App\Http\Controllers\UserController@deleteUser');

Route::get('/units', 'App\Http\Controllers\LearningUnitController@showLearningUnits');
Route::get('/units/{id}', 'App\Http\Controllers\LearningUnitController@showLearningUnitById');
Route::post('/units', 'App\Http\Controllers\LearningUnitController@createLearningUnit');
Route::put('/units/{id}', 'App\Http\Controllers\LearningUnitController@updateLearningUnit');
Route::delete('/units/{id}', 'App\Http\Controllers\LearningUnitController@deleteLearningUnit');

Route::get('/units/{unitId}/levels', 'App\Http\Controllers\LevelController@showLevels');
Route::get('/units/{unitId}/levels/{levelId}', 'App\Http\Controllers\LevelController@showLevelById');
Route::post('/units/{unitId}/levels/', 'App\Http\Controllers\LevelController@createLevel');
Route::put('/units/{unitId}/levels/{levelId}', 'App\Http\Controllers\LevelController@updateLevel');
Route::delete('/units/{unitId}/levels/{levelId}', 'App\Http\Controllers\LevelController@deleteLevel');

Route::get('login/google', [FirebaseLoginController::class, 'redirectToGoogle']);
Route::get('login/google/callback', [FirebaseLoginController::class, 'handleGoogleCallback'])->name('login.google.callback');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/loginAdmin', function () {
    return view('loginAdmin');
})->name('loginAdmin');

Route::middleware(CheckFirebaseRole::class)->group(function () {
    Route::get('admin-page', function(){
        return view('admin.index');
    });
    Route::get('/logoutAdmin', [FirebaseLoginController::class, 'logout'])->name('logoutAdmin');
    Route::get('/kelolaPengguna', [AdminController::class, 'showUsers'])->name('kelolaPengguna');
    
    Route::get('/modifikasiMateri', function () {
        return view('admin.layouts.modifikasiMateri');
    });
    
    Route::get('/perkembanganPengguna', function () {
        return view('admin.layouts.perkembanganPengguna');
    });

    Route::get('/catatanAdmin', function () {
        return view('admin.layouts.catatanAdmin');
    });

    Route::put('/admin-page/users/{noWhatsapp}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/admin-page/users/{noWhatsapp}', [AdminController::class, 'deleteUser'])->name('users.delete');
});

Route::get('/unauthorizedAccess', function () {
    return view('admin.layouts.unauthorizedAccess');
});


require __DIR__.'/auth.php';
