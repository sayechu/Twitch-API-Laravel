<?php

namespace App\Infrastructure\UnfollowStreamer;

use App\Exceptions\InternalServerErrorException;
use App\Infrastructure\Controllers\Controller;
use App\Exceptions\NotFoundException;
use Illuminate\Http\JsonResponse;
use App\Services\UnfollowManager;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class AnalyticsUnfollowController extends Controller
{
    private UnfollowManager $unfollowManager;

    public function __construct(UnfollowManager $unfollowManager)
    {
        $this->unfollowManager = $unfollowManager;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $streamerId = $request->input('streamerId');

        try {
            $unfollowResponse = $this->unfollowManager->unfollowStreamer($username, $streamerId);
            return response()->json($unfollowResponse, Response::HTTP_OK);
        } catch (NotFoundException $e) {
            return response()->json($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (InternalServerErrorException $e) {
            return response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
