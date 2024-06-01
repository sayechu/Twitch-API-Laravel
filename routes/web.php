<?php

use App\Infrastructure\FollowStreamer\AnalyticsFollowStreamerController;
use App\Infrastructure\GetStreams\AnalyticsStreamsController;
use App\Infrastructure\GetTopsOfTheTops\AnalyticsTopsOfTheTopsController;
use App\Infrastructure\GetStreamers\AnalyticsStreamersController;
use App\Infrastructure\UnfollowStreamer\AnalyticsUnfollowController;
use App\Infrastructure\CreateUser\AnalyticsCreateUserController;
use App\Infrastructure\GetUsers\AnalyticsGetUsersController;
use App\Infrastructure\Timeline\AnalyticsTimelineController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/analytics/streamers', AnalyticsStreamersController::class);
Route::get('/analytics/streams', AnalyticsStreamsController::class);
Route::get('/analytics/topsofthetops', AnalyticsTopsOfTheTopsController::class);
Route::post('/analytics/follow', AnalyticsFollowStreamerController::class);
Route::delete('/analytics/unfollow', AnalyticsUnfollowController::class);
Route::post('/analytics/users', AnalyticsCreateUserController::class);
Route::get('/analytics/users', AnalyticsGetUsersController::class);
Route::get('/analytics/timeline', AnalyticsTimelineController::class);
