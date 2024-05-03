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
        $responseGetToken = $this->apiClient->getToken();
        $twitchToken = json_decode($responseGetToken, true)['access_token'];

        $apiHeaders = array(
            'Authorization: Bearer ' . $twitchToken,
        );

        $responseGetStreams = $this->apiClient->getStreamsCall($apiHeaders);
        return json_decode($responseGetStreams, true)['data'];
    }
}
