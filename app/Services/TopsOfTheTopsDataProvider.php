<?php

namespace App\Services;

class TopsOfTheTopsDataProvider
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;

    public function __construct(ApiClient $apiClient, DBClient $databaseClient)
    {
        $this->apiClient = $apiClient;
        $this->databaseClient = $databaseClient;
    }

    public function getTopThreeGames(array $apiHeaders): array
    {
        $apiUrl = "https://api.twitch.tv/helix/games/top?first=3";

        $topGamesResponse = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

        return json_decode($topGamesResponse['response'], true)['data'];
    }

    public function getTopFourtyVideos(string $gameId, array $apiHeaders): array
    {
        $api_url = "https://api.twitch.tv/helix/videos?game_id=$gameId&sort=views&first=40";

        $topVideosData = $this->apiClient->makeCurlCall($api_url, $apiHeaders);

        return json_decode($topVideosData['response'], true)['data'];
    }

    public function isThereAnyStoredGame(): bool
    {
        return $this->databaseClient->isLoadedDB();
    }
}
