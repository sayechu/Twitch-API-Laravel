<?php

namespace App\Infrastructure\FollowStreamer;

use App\Services\FollowStreamerManager;
use App\Infrastructure\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\InternalServerErrorException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use Illuminate\Http\Response;

class AnalyticsFollowStreamerController extends Controller
{
    private FollowStreamerManager $followManager;

    public function __construct(FollowStreamerManager $followManager)
    {
        $this->followManager = $followManager;
    }

    public function __invoke(AnalyticsFollowStreamerRequest $request): JsonResponse
    {
        $username = $request->input('username');
        $streamerId = $request->input('streamerId');

        try {
            $followMessage = $this->followManager->getFollowMessage($username, $streamerId);
            return response()->json($followMessage, Response::HTTP_OK);
        } catch (ConflictException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (ForbiddenException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (NotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (UnauthorizedException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (InternalServerErrorException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
