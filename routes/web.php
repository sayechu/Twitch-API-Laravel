<?php

use Illuminate\Support\Facades\Route;
use App\Infrastructure\Controllers\AnalyticsUsersController;
use App\Infrastructure\Controllers\GetStreamsController;
use App\Infrastructure\Controllers\AnalyticsTopsOfTheTopsController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/analytics/users', AnalyticsUsersController::class);
Route::get('/analytics/streams', GetStreamsController::class);
Route::get('/analytics/topsofthetops', AnalyticsTopsOfTheTopsController::class);
