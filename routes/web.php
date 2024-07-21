<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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

Route::get('/units/{unitId}/levels', 'App\Http\Controllers\LearningUnitController@showLearningUnitById');
Route::get('/units/{unitId}/levels/{levelId}', 'App\Http\Controllers\LearningUnitController@showLearningUnitById');
Route::post('/units/{unitId}', 'App\Http\Controllers\LearningUnitController@createLearningUnit');
Route::put('/units/{unitId}', 'App\Http\Controllers\LearningUnitController@updateLearningUnit');
Route::delete('/units/{unitId}/levels/{levelId}', 'App\Http\Controllers\LearningUnitController@deleteLearningUnit');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/loginAdmin', function () {
    return view('loginAdmin');
})->name('loginAdmin');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('admin-page', function(){
    return view('admin.index');
});

Route::get('/kelolaData', function () {
    return view('admin.layouts.kelolaData');
});

Route::get('/hapusData', function () {
    return view('admin.layouts.hapusData');
});

Route::get('/modifikasiMateri', function () {
    return view('admin.layouts.modifikasiMateri');
});

Route::get('/perkembanganPengguna', function () {
    return view('admin.layouts.perkembanganPengguna');
});

require __DIR__.'/auth.php';
