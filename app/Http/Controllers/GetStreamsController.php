<?php

namespace App\Http\Controllers;

use App\Services\StreamsDataManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetStreamsController extends Controller
{
    private StreamsDataManager $streamsDataManager;
    public function __construct(StreamsDataManager $streamsDataManager)
    {
        $this->streamsDataManager = $streamsDataManager;
    }

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($this->streamsDataManager->getStreamsData());
    }
}
