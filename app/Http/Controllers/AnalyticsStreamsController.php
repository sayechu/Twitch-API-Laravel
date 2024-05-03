<?php

namespace App\Http\Controllers;

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
        $streams = $this->getStreamsService->getStreams();

        return response()->json($streams);
    }
}
