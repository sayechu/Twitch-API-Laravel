<?php

namespace App\Infrastructure\GetUsers;

use App\Exceptions\InternalServerErrorException;
use App\Infrastructure\Controllers\Controller;
use App\Services\GetUsersManager;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsGetUsersController extends Controller
{
    private GetUsersManager $getUsersManager;

    public function __construct(GetUsersManager $getUsersManager)
    {
        $this->getUsersManager = $getUsersManager;
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $createUserMessage = $this->getUsersManager->getUsersAndStreamers();
            return response()->json($createUserMessage, Response::HTTP_OK);
        } catch (InternalServerErrorException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
