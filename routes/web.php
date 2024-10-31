<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', [ChatController::class, 'loadDashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/check-channel', [ChatController::class, 'CheckChannel'])->middleware(['auth', 'verified'])->name('');

Route::get('/create-channel', [ChatController::class, 'CreateChannel'])->middleware(['auth', 'verified'])->name('');

Route::get('save-message', [ChatController::class, 'save_message'])->middleware(['auth', 'verified'])->name('');
Route::get('load-chat', [ChatController::class, 'load_chat'])->middleware(['auth', 'verified'])->name('');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/image-upload', [ProfileController::class, 'uploadImage'])->name('profile.image.upload');
    Route::get('/user-avatars', [ChatController::class, 'getUserAvatars']);
});

require __DIR__.'/auth.php';
