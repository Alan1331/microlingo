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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
