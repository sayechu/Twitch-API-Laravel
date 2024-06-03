<?php

namespace App\Services;

use App\Exceptions\InternalServerErrorException;
use Illuminate\Http\Response;

class TimelineStreamsProvider
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;
    private const STREAMS_URL = 'https://api.twitch.tv/helix/videos';
    private const INTERNAL_SERVER_ERROR_MESSAGE = 'Error del servidor al obtener el timeline.';

    public function __construct(ApiClient $apiClient, DBClient $databaseClient)
    {
        $this->apiClient = $apiClient;
        $this->databaseClient = $databaseClient;
    }

    public function getTimelineStreams(string $twitchToken, array $followingStreamers): array
    {
        foreach ($followingStreamers as $streamerId) {
            $apiUrl = self::STREAMS_URL . '?user_id=' . $streamerId;
            $apiHeaders = ['Authorization: Bearer ' . $twitchToken];

            $streamsResponse = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

            if ($this->requestHas500Code($streamsResponse)) {
                throw new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_MESSAGE);
            }

            $this->databaseClient->storeStreams($streamsResponse['response']['data']);
        }
        return $this->databaseClient->getTimelineStreams();
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
