<?php

namespace App\Infrastructure\FollowStreamer;

use App\Http\Requests\AnalyticsFollowStreamerRequest;
use App\Services\FollowStreamerManager;
use Illuminate\Http\Request;
use App\Infrastructure\Controllers\Controller;

class AnalyticsFollowStreamerController extends Controller
{
    private FollowStreamerManager $followManager;

    public function __construct(FollowStreamerManager $followManager)
    {
        $this->$followManager = $followManager;
    }
    public function __invoke(AnalyticsFollowStreamerRequest $request)
    {
        $userId = $request->input('userId');
        $streamerId = $request->input('streamerId');

        $followMessage = $this->followManager->getFollowMessage($userId, $streamerId);
        return response()->json($followMessage);
    }
}
