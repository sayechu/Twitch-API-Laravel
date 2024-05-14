<?php

use App\Infrastructure\GetStreams\AnalyticsStreamsController;
use App\Infrastructure\GetTopsOfTheTops\AnalyticsTopsOfTheTopsController;
use App\Infrastructure\GetUsers\AnalyticsUsersController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/analytics/users', AnalyticsUsersController::class);
Route::get('/analytics/streams', AnalyticsStreamsController::class);
Route::get('/analytics/topsofthetops', AnalyticsTopsOfTheTopsController::class);
