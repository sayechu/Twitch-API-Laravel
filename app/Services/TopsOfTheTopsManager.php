<?php

namespace App\Services;

class TopsOfTheTopsManager
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getTop40VideosDadoUnGameId($gameId) : array
    {
        $api_url = "https://api.twitch.tv/helix/videos?game_id={$gameId}&sort=views&first=40";

        $responseGetToken = $this->apiClient->getToken();
        $twitchToken = json_decode($responseGetToken, true)['access_token'];

        $api_headers = array(
            'Authorization: Bearer ' . $twitchToken,
        );

        $topVideosData = $this->apiClient->makeCurlCall($api_url, $api_headers);

        return json_decode($topVideosData, true);
    }

    public function getTopGames() : array
    {
        $api_url = "https://api.twitch.tv/helix/games/top?first=3";

        $responseGetToken = $this->apiClient->getToken();
        $twitchToken = json_decode($responseGetToken, true)['access_token'];

        $api_headers = array(
            'Authorization: Bearer ' . $twitchToken,
        );

        $topGamesData = $this->apiClient->makeCurlCall($api_url, $api_headers);

        return json_decode($topGamesData, true);
    }
}
