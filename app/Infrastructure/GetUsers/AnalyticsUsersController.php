<?php

namespace App\Infrastructure\GetUsers;

use Illuminate\Http\Response;
use App\Infrastructure\Controllers\Controller;
use App\Services\UserDataManager;
use Illuminate\Http\JsonResponse;

class AnalyticsUsersController extends Controller
{
    private UserDataManager $userDataManager;

    public function __construct(UserDataManager $userDataManager)
    {
        $this->userDataManager = $userDataManager;
    }

    public function __invoke(AnalyticsUsersRequest $request): JsonResponse
    {
        $userId = $request->input('id');

        $userData = $this->userDataManager->getUserData($userId);

        if ($this->containsServerError($userData))
            return response()->json($userData, Response::HTTP_SERVICE_UNAVAILABLE);

        return response()->json($userData);
    }

    private function containsServerError(array $userData): bool
    {
        return isset($userData['error']);
    }
}
