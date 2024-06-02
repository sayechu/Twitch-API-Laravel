<?php

namespace App\Services;

use App\Exceptions\InternalServerErrorException;
use Illuminate\Http\Response;

class TimelineStreamsProvider
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;
    private const STREAMS_URL = 'https://api.twitch.tv/helix/streams';

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

            if ($this->requestHas500Code($twitchToken)) {
                throw new InternalServerErrorException('Error del servidor al obtener el timeline.');
            }

            $streamerStreams = json_decode($streamsResponse['response'], true)['data'];
            $this->databaseClient->storeStreams($streamerStreams);
        }
        return $this->databaseClient->getTimelineStreams();
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
