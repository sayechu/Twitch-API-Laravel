<?php

namespace App\Services;

use Illuminate\Http\Response;

class StreamsDataManager
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;
    private const ERROR_GET_TOKEN_FAILED = 'No se puede establecer conexión con Twitch en este momento';
    private const ERROR_GET_STREAMS_FAILED = 'No se pueden devolver streams en este momento, inténtalo más tarde';

    public function __construct(TokenProvider $tokenProvider, ApiClient $apiClient)
    {
        $this->tokenProvider = $tokenProvider;
        $this->apiClient = $apiClient;
    }

    public function getStreamsData(): array
    {
        $twitchTokenResponse = $this->tokenProvider->getToken();

        if ($this->requestHas500Code($twitchTokenResponse)) {
            return ['error' => self::ERROR_GET_TOKEN_FAILED];
        }

        $apiHeaders = ['Authorization: Bearer ' . $twitchTokenResponse];
        $apiUrl = 'https://api.twitch.tv/helix/streams';

        $streamsResponse = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

        if ($this->requestHas500Code($streamsResponse)) {
            return ['error' => self::ERROR_GET_STREAMS_FAILED];
        }

        return $streamsResponse['response']['data'];
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
