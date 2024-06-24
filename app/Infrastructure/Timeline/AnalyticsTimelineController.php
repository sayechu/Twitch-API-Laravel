<?php

namespace App\Infrastructure\Timeline;

use App\Exceptions\InternalServerErrorException;
use App\Exceptions\NotFoundException;
use App\Infrastructure\Controllers\Controller;
use App\Services\GetTimelineManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AnalyticsTimelineController extends Controller
{
    private GetTimelineManager $timelineManager;

    public function __construct(GetTimelineManager $timelineManager)
    {
        $this->timelineManager = $timelineManager;
    }

    public function __invoke(AnalyticsTimelineRequest $request): JsonResponse
    {
        try {
            $username = $request->input('username');
            $streamersTimeline = $this->timelineManager->getStreamersTimeline($username);
            return response()->json($streamersTimeline, Response::HTTP_OK);
        } catch (NotFoundException $exception) {
            return response()->json(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (InternalServerErrorException $exception) {
            return response()->json(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
