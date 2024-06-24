<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Response;

class StreamsDataManager
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;
    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    private const GET_STREAMS_ERROR_MESSAGE = 'No se pueden devolver streams en este momento, inténtalo más tarde';
    private const STREAMS_URL = 'https://api.twitch.tv/helix/streams';

    public function __construct(TokenProvider $tokenProvider, ApiClient $apiClient)
    {
        $this->tokenProvider = $tokenProvider;
        $this->apiClient = $apiClient;
    }

    public function getStreamsData(): array
    {
        $twitchTokenResponse = $this->tokenProvider->getToken();

        if ($this->requestHas500Code($twitchTokenResponse)) {
            throw new Exception(self::GET_TOKEN_ERROR_MESSAGE);
        }

        $apiHeaders = ['Authorization: Bearer ' . $twitchTokenResponse];
        $apiUrl = self::STREAMS_URL;

        $streamsResponse = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

        if ($this->requestHas500Code($streamsResponse)) {
            throw new Exception(self::GET_STREAMS_ERROR_MESSAGE);
        }

        return $streamsResponse['response']['data'];
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
