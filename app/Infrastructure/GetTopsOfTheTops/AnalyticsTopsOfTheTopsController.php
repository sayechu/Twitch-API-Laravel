<?php

namespace App\Infrastructure\GetTopsOfTheTops;

use App\Infrastructure\Controllers\Controller;
use App\Services\GetTopsOfTheTopsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AnalyticsTopsOfTheTopsController extends Controller
{
    private GetTopsOfTheTopsService $getTopsOfTopsService;

    public function __construct(GetTopsOfTheTopsService $getTopsOfTopsService)
    {
        $this->getTopsOfTopsService = $getTopsOfTopsService;
    }

    public function __invoke(AnalyticsTopsOfTheTopsRequest $request): JsonResponse
    {
        $since = $request->input('since') ?? (10 * 60);

        try {
            $topsOfTheTops = $this->getTopsOfTopsService->execute($since);
            return response()->json($topsOfTheTops);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
