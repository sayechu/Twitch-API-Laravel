<?php

use App\Infrastructure\CreateUser\AnalyticsCreateUserController;
use App\Infrastructure\FollowStreamer\AnalyticsFollowStreamerController;
use App\Infrastructure\GetStreams\AnalyticsStreamsController;
use App\Infrastructure\GetTopsOfTheTops\AnalyticsTopsOfTheTopsController;
use App\Infrastructure\GetStreamers\AnalyticsStreamersController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/analytics/streamers', AnalyticsStreamersController::class);
Route::get('/analytics/streams', AnalyticsStreamsController::class);
Route::get('/analytics/topsofthetops', AnalyticsTopsOfTheTopsController::class);
Route::post('/analytics/follow', AnalyticsFollowStreamerController::class);
Route::post('/analytics/users', AnalyticsCreateUserController::class);
