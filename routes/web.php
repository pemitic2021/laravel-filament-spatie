<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Pages\Auth\VerifyEmailCode;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/verify', VerifyEmailCode::class)
    ->name('verification.notice');