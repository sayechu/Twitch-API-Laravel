<?php

namespace App\Services;

use Illuminate\Http\Response;

class TopGamesProvider
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

        if ($this->requestHas500Code($topGamesResponse)) {
            return $topGamesResponse;
        }

        $this->storeTopGames($topGamesResponse['response']['data']);

        return $topGamesResponse['response']['data'];
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function storeTopGames(mixed $topThreeGames): void
    {
        foreach ($topThreeGames as $topGame) {
            if (!$this->databaseClient->isGameStored($topGame['id'])) {
                $this->databaseClient->storeTopGame($topGame);
            }
        }
    }
}
