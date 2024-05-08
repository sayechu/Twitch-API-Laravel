<?php

namespace App\Infrastructure\Controllers;

use App\Services\GetStreamsService;
use App\Services\StreamsDataManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetStreamsController extends Controller
{
    private GetStreamsService $getStreamsService;

    public function __construct(GetStreamsService $getStreamsService)
    {
        $this->getStreamsService = $getStreamsService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($this->getStreamsService->execute());
    }
}
