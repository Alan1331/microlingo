<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FirebaseLoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckFirebaseRole;
use App\Http\Middleware\ReplyUser;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/whatsapp/send', 'App\Http\Controllers\WhatsAppController@sendMessage');
Route::post('/whatsapp/receive',
    'App\Http\Controllers\WhatsAppController@receiveMessage'
)->middleware(ReplyUser::class);
Route::get('/whatsapp/status-callback', 'App\Http\Controllers\WhatsAppController@statusCallback');

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
    Route::put('/admin-page/users/{noWhatsapp}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/admin-page/users/{noWhatsapp}', [AdminController::class, 'deleteUser'])->name('users.delete');

    Route::get('/materiPembelajaran', [AdminController::class, 'showLearningUnits'])->name('materiPembelajaran');
    Route::post('/materiPembelajaran', [AdminController::class, 'createLearningUnit'])->name('units.create');
    Route::get('/materiPembelajaran/{id}', [AdminController::class, 'showLearningUnitById'])->name('units.levels');
    Route::put('/materiPembelajaran/{id}/levels/{levelId}', [AdminController::class, 'updateLevel'])->name('units.levels.update');
    Route::put('/materiPembelajaran/{id}/levels/{levelId}/videos', [AdminController::class, 'uploadVideo'])->name('units.levels.videos');
    Route::delete('/materiPembelajaran/{id}', [AdminController::class, 'deleteUnit'])->name('units.delete');
    Route::delete('/materiPembelajaran/{id}/levels/{levelId}', [AdminController::class, 'deleteLevel'])->name('units.levels.delete');
});

Route::get('/unauthorizedAccess', function () {
    return view('admin.layouts.unauthorizedAccess');
});


require __DIR__.'/auth.php';
