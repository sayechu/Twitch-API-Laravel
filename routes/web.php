<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyticsUsersController;
use App\Http\Controllers\AnalyticsStreamsController;

Route::get('/', function () {
    return view('welcome');
});
 
Route::get('/analytics/users', AnalyticsUsersController::class);

Route::get('/analytics/streams', AnalyticsStreamsController::class);