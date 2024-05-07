<?php

namespace App\Http\Controllers;

use App\Services\StreamsDataManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsStreamsController extends Controller
{
    private StreamsDataManager $streamsDataManager;
    public function __construct(StreamsDataManager $streamsDataManager)
    {
        $this->streamsDataManager = $streamsDataManager;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $streams = $this->streamsDataManager->getStreamsData();

        return response()->json($streams);
    }
}
