<?php

use Illuminate\Support\Facades\Route;

Route::get('/home', function () {
    return 'تم تسجيل الخروج';
})->name('home');
