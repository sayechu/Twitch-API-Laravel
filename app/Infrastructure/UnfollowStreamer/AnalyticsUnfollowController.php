<?php

namespace App\Infrastructure\UnfollowStreamer;

use App\Exceptions\InternalServerErrorException;
use App\Exceptions\NotFoundException;
use App\Services\UnfollowManager;
use Illuminate\Http\Response;
use App\Infrastructure\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsUnfollowController extends Controller
{
    private UnfollowManager $unfollowManager;
    private const ERROR_500_MESSAGE = 'Error del servidor al dejar de seguir al streamer.';

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
            return response()->json(self::ERROR_500_MESSAGE, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
