<?php

namespace App\Infrastructure\GetStreamers;

use Exception;
use Illuminate\Http\Response;
use App\Infrastructure\Controllers\Controller;
use App\Services\UserDataManager;
use Illuminate\Http\JsonResponse;

class AnalyticsStreamersController extends Controller
{
    private UserDataManager $userDataManager;

    public function __construct(UserDataManager $userDataManager)
    {
        $this->userDataManager = $userDataManager;
    }

    public function __invoke(AnalyticsStreamersRequest $request): JsonResponse
    {
        $userId = $request->input('id');

        try {
            $userData = $this->userDataManager->getUserData($userId);
            return response()->json($userData);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
