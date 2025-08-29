<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::get('/signup', function () {
    return view('auth.register');
})->name('register');

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');
