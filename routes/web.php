<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\FirebaseLoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LearningUnitController;
use App\Http\Controllers\LevelController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckFirebaseRole;
use App\Http\Middleware\ReplyUser;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/whatsapp/receive/test', 'App\Http\Controllers\WhatsAppController@receiveMessage');
Route::post('/whatsapp/receive',
    'App\Http\Controllers\WhatsAppController@receiveMessage'
)->middleware(ReplyUser::class);

Route::get('login/google', [FirebaseLoginController::class, 'redirectToGoogle']);
Route::get('login/google/callback', [FirebaseLoginController::class, 'handleGoogleCallback'])->name('login.google.callback');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/loginAdmin', function () {
    return view('loginAdmin');
})->name('loginAdmin');

Route::middleware(CheckFirebaseRole::class)->group(function () {
    Route::get('/admin-page', [AdminController::class, 'showDashboard']);
    Route::get('/logoutAdmin', [FirebaseLoginController::class, 'logout'])->name('logoutAdmin');

    Route::get('/users', [UserController::class, 'showUsers'])->name('kelolaPengguna');
    Route::get('/levels/{levelId}', [LevelController::class, 'showLevelById'])->name('units.levels.show');
    Route::put('/levels/{levelId}', [LevelController::class, 'updateLevel'])->name('units.levels.update');

    Route::get('/materiPembelajaran', [LearningUnitController::class, 'showLearningUnits'])->name('materiPembelajaran');
    Route::post('/materiPembelajaran', [LearningUnitController::class, 'createLearningUnit'])->name('units.create');
    Route::get('/materiPembelajaran/{id}', [LearningUnitController::class, 'showLearningUnitById'])->name('units.levels');
    Route::put('/materiPembelajaran/{id}', [LearningUnitController::class, 'updateLearningUnit'])->name('units.update');
    Route::delete('/materiPembelajaran/{id}', [LearningUnitController::class, 'deleteUnit'])->name('units.delete');
});

Route::get('/unauthorizedAccess', function () {
    return view('admin.layouts.unauthorizedAccess');
});

require __DIR__.'/auth.php';
