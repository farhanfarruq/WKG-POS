<?php

use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\UserAvatarController;
use Illuminate\Support\Facades\Route;

Route::get('/user-avatars/{path}', UserAvatarController::class)
    ->where('path', '.*')
    ->name('user-avatars.show');

Route::get('/product-images/{path}', ProductImageController::class)
    ->where('path', '.*')
    ->name('product-images.show');

Route::get('/', function () {
    return redirect()->route('pos.index');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/attendance', function () {
    return view('attendance.index');
})->name('attendance.index');

Route::get('/pos', function () {
    return view('pos.index');
})->name('pos.index');

Route::get('/kds', function () {
    return view('kds.index');
})->name('kds.index');

Route::prefix('shift')->name('shift.')->group(function () {
    Route::get('/open', function () {
        return view('shift.open');
    })->name('open');

    Route::get('/close', function () {
        return view('shift.close');
    })->name('close');
});
