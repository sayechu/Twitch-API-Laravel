<?php

namespace App\Infrastructure\FollowStreamer;

use App\Http\Requests\AnalyticsFollowRequest;
use App\Services\FollowStreamerManager;
use Illuminate\Http\Request;
use App\Infrastructure\Controllers\Controller;

class AnalyticsFollowController extends Controller
{
    private FollowStreamerManager $followManager;

    public function __construct(FollowStreamerManager $followManager)
    {
        $this->$followManager = $followManager;
    }
    public function __invoke(AnalyticsFollowRequest $request)
    {
        $userId = $request->input('userId');
        $streamerId = $request->input('streamerId');

        $followConfirmationMessage = $this->followManager->getFollowMessage($userId, $streamerId);
        return response()->json($followConfirmationMessage);
    }
}
