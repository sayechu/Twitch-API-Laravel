<?php

namespace App\Infrastructure\UnfollowStreamer;

use App\Exceptions\ForbiddenException;
use App\Exceptions\InternalServerErrorException;
use App\Exceptions\NotFoundException;
use App\Services\UnfollowManager;
use Exception;
use Illuminate\Http\Response;
use App\Infrastructure\Controllers\Controller;
use Illuminate\Http\JsonResponse;
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
        $userId = $request->input('userId');
        $streamerId = $request->input('streamerId');

        try {
            $unfollowResponse = $this->unfollowManager->unfollowStreamer($userId, $streamerId);
            return response()->json($unfollowResponse, Response::HTTP_OK);
        } catch (ForbiddenException $e) {
            return response()->json($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (NotFoundException $e) {
            return response()->json($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (InternalServerErrorException $e) {
            return response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
