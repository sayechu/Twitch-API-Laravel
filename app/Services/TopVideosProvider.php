<?php

namespace App\Services;

use Illuminate\Http\Response;

class TopVideosProvider
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;

    public function __construct(ApiClient $apiClient, DBClient $databaseClient)
    {
        $this->apiClient = $apiClient;
        $this->databaseClient = $databaseClient;
    }

    public function getTopFourtyVideos(string $topGameId, string $gameName, int $since, array $apiHeaders): array
    {
        if ($this->databaseClient->isDataStoredRecentlyFromGame($topGameId, $since)) {
            return $this->databaseClient->getVideosOfAGivenGame($topGameId);
        }

        $apiUrl = "https://api.twitch.tv/helix/videos?game_id=$topGameId&sort=views&first=40";

        $top40VideosResponse = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

        if ($this->requestHas500Code($top40VideosResponse)) {
            return $top40VideosResponse;
        }

        $this->databaseClient->updateTopGameLastUpdateTime($topGameId);
        $this->databaseClient->updateTopGameVideos($top40VideosResponse['response']['data'], $topGameId, $gameName);

        return $this->databaseClient->getVideosOfAGivenGame($topGameId);
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
