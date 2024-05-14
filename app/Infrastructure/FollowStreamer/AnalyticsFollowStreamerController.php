<?php

namespace App\Infrastructure\FollowStreamer;

use App\Services\FollowStreamerManager;
use App\Infrastructure\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsFollowStreamerController extends Controller
{
    private FollowStreamerManager $followManager;

    public function __construct(FollowStreamerManager $followManager)
    {
        $this->followManager = $followManager;
    }
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->input('userId');
        $streamerId = $request->input('streamerId');

        $followMessage = $this->followManager->getFollowMessage($userId, $streamerId);
        return response()->json($followMessage);
    }
}
