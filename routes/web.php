<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyticsUsersController;

Route::get('/', function () {
    return view('welcome');
});
 
Route::get('/users', AnalyticsUsersController::class);