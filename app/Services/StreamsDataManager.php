<?php

namespace App\Services;

use Illuminate\Http\Response;

class StreamsDataManager
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;

    public function __construct(TokenProvider $tokenProvider, ApiClient $apiClient)
    {
        $this->tokenProvider = $tokenProvider;
        $this->apiClient = $apiClient;
    }

    public function getStreamsData(): string|array
    {
        $twitchTokenResponse = $this->tokenProvider->getToken();

        if ($this->requestHas500Code($twitchTokenResponse)) {
            return '503: {"error": "No se puede establecer conexión con Twitch en este momento}';
        }

        $apiHeaders = ['Authorization: Bearer ' . $twitchTokenResponse];
        $apiUrl = 'https://api.twitch.tv/helix/streams';

        $streamsResponse = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

        if ($this->requestHas500Code($streamsResponse)) {
            return '503: {"error": "No se pueden devolver streams en este momento, inténtalo más tarde"}';
        }

        return json_decode($streamsResponse['response'], true)['data'];
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
