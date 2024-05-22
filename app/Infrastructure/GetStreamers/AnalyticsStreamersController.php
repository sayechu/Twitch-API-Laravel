<?php

namespace App\Infrastructure\GetStreamers;

use Exception;
use Illuminate\Http\Response;
use App\Infrastructure\Controllers\Controller;
use App\Services\StreamersDataManager;
use Illuminate\Http\JsonResponse;

class AnalyticsStreamersController extends Controller
{
    private StreamersDataManager $streamerDataManager;

    public function __construct(StreamersDataManager $userDataManager)
    {
        $this->streamerDataManager = $userDataManager;
    }

    public function __invoke(AnalyticsStreamersRequest $request): JsonResponse
    {
        $userId = $request->input('id');

        try {
            $streamerData = $this->streamerDataManager->getStreamerData($userId);
            return response()->json($streamerData);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
