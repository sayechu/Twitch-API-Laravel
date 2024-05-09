<?php

namespace App\Infrastructure\Controllers;

use App\Infrastructure\Requests\AnalyticsUsersRequest;
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

        return response()->json($userData);
    }
}
