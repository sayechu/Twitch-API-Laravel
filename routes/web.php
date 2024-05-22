<?php

use App\Infrastructure\Controllers\AnalyticsStreamsController;
use App\Infrastructure\Controllers\AnalyticsTopsOfTheTopsController;
use App\Infrastructure\FollowStreamer\AnalyticsFollowStreamerController;
use App\Infrastructure\UnfollowStreamer\AnalyticsUnfollowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/analytics/streams', AnalyticsStreamsController::class);
Route::get('/analytics/topsofthetops', AnalyticsTopsOfTheTopsController::class);
Route::get('/analytics/follow', AnalyticsFollowStreamerController::class);
Route::delete('/analytics/unfollow', AnalyticsUnfollowController::class);
