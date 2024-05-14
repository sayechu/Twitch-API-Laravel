<?php

namespace App\Services;

class FollowStreamerManager
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;
    private const GET_ID_DATA_URL = 'https://api.twitch.tv/helix/users';

    public function __construct(TokenProvider $tokenProvider, ApiClient $apiClient)
    {
        $this->tokenProvider = $tokenProvider;
        $this->apiClient = $apiClient;
    }

    public function getFollowMessage($userId, $streamerId)
    {
        $twitchToken = $this->tokenProvider->getToken();

        $userIdNumeric = $this->getIdNumeric($userId, $twitchToken);
        $streamerIdNumeric = $this->getIdNumeric($streamerId, $twitchToken);

        return $this->followStreamer($userIdNumeric, $streamerIdNumeric, $twitchToken);
    }

    private function getIdNumeric($twitchId, $twitchToken)
    {
        $apiUrl = self::GET_ID_DATA_URL . '?login=' . urlencode($twitchId);
        $apiHeaders = ['Authorization: Bearer ' . $twitchToken];

        $twitchIdResponse = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

        return $this->extractIdNumeric($twitchIdResponse);
    }

    private function extractIdNumeric($twitchIdResponse)
    {
        return json_decode($twitchIdResponse['response'], true)['data'][0]['id'];
    }
}
