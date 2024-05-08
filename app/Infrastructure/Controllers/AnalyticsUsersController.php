<?php

namespace App\Infrastructure\Controllers;

use App\Http\Requests\AnalyticsUsersRequest;
use App\Services\GetUsersService;
use Illuminate\Http\JsonResponse;

class AnalyticsUsersController extends Controller
{
    private GetUsersService $getUserService;
    public function __construct(GetUsersService $getUserService)
    {
        $this->getUserService = $getUserService;
    }

    public function __invoke(AnalyticsUsersRequest $request): JsonResponse
    {
        $userId = $request->input('id');

        $userInfo = $this->getUserService->getUserInfoById($userId);

        return response()->json($userInfo);
    }
}
