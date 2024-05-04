<?php

namespace App\Services;

class StreamsManager
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getStreams(): array
    {
        $api_url = 'https://api.twitch.tv/helix/streams';

        $responseGetToken = $this->apiClient->getToken();
        $twitchToken = json_decode($responseGetToken, true)['access_token'];

        $apiHeaders = array(
            'Authorization: Bearer ' . $twitchToken,
        );

        $responseGetStreams = $this->apiClient->makeCurlCall($api_url, $apiHeaders);
        return json_decode($responseGetStreams, true)['data'];
    }
}
