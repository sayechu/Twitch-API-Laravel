<?php

namespace App\Services;

use Illuminate\Http\Response;
use Mockery\Exception;

class StreamersDataManager
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;

    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    private const GET_STREAMERS_ERROR_MESSAGE = 'No se pueden devolver usuarios en este momento, inténtalo más tarde';
    private const GET_STREAMERS_URL = 'https://api.twitch.tv/helix/users';


    public function __construct(TokenProvider $tokenProvider, ApiClient $apiClient)
    {
        $this->tokenProvider = $tokenProvider;
        $this->apiClient = $apiClient;
    }

    public function getStreamerData(string $streamerId): array
    {
        $twitchToken = $this->tokenProvider->getToken();

        if ($this->requestHas500Code($twitchToken)) {
            throw new Exception(self::GET_TOKEN_ERROR_MESSAGE);
        }

        $apiUrl = self::GET_STREAMERS_URL . "?id=" . urlencode($streamerId);
        $apiHeaders = ['Authorization: Bearer ' . $twitchToken];

        $streamerData = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

        if ($this->requestHas500Code($streamerData)) {
            throw new Exception(self::GET_STREAMERS_ERROR_MESSAGE);
        }

        return json_decode($streamerData['response'], true);
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
