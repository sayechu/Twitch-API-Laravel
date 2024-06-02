<?php

namespace App\Infrastructure\Controllers;

use App\Services\GetStreamsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsStreamsController extends Controller
{
    private GetStreamsService $getStreamsService;
    private const ERROR_STATUS_CODE = 503;

    public function __construct(GetStreamsService $getStreamsService)
    {
        $this->getStreamsService = $getStreamsService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $streamsData = $this->getStreamsService->execute();

        if ($this->containsServerError($streamsData)) {
            return response()->json($streamsData, self::ERROR_STATUS_CODE);
        }

        return response()->json($streamsData);
    }

    private function containsServerError(array $streamsData): bool
    {
        return isset($streamsData['error']);
    }
}
