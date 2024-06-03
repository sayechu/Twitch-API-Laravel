<?php

namespace App\Services;

use App\Exceptions\InternalServerErrorException;
use App\Exceptions\NotFoundException;
use Illuminate\Http\Response;

class GetTimelineManager
{
    private TokenProvider $tokenProvider;
    private TimelineStreamersProvider $streamersProvider;
    private TimelineStreamsProvider $streamsProvider;
    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';

    public function __construct(
        TokenProvider $tokenProvider,
        TimelineStreamersProvider $streamersProvider,
        TimelineStreamsProvider $streamsProvider
    ) {
        $this->tokenProvider = $tokenProvider;
        $this->streamersProvider = $streamersProvider;
        $this->streamsProvider = $streamsProvider;
    }

    /**
     * @throws NotFoundException
     * @throws InternalServerErrorException
     */
    public function getStreamersTimeline(string $username): array
    {
        $followingStreamers = $this->streamersProvider->getTimelineStreamers($username);

        $twitchToken = $this->tokenProvider->getToken();

        if ($this->requestHas500Code($twitchToken)) {
            throw new InternalServerErrorException(self::GET_TOKEN_ERROR_MESSAGE);
        }

        return $this->streamsProvider->getTimelineStreams($twitchToken, $followingStreamers);
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
