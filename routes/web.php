<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
// Route::get('/', function () {
//     return view('app');
// });
Route::get('/', function () {
    return Inertia::render('Home');
});