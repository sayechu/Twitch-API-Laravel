<?php

namespace App\Infrastructure\GetStreams;

use Exception;
use Illuminate\Http\Response;
use App\Infrastructure\Controllers\Controller;
use App\Services\GetStreamsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsStreamsController extends Controller
{
    private GetStreamsService $getStreamsService;

    public function __construct(GetStreamsService $getStreamsService)
    {
        $this->getStreamsService = $getStreamsService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $streamsData = $this->getStreamsService->execute();
            return response()->json($streamsData);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
