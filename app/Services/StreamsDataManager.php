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

    public function getStreamsData(): array | string
    {
        $api_url = 'https://api.twitch.tv/helix/streams';
        $token = $this->tokenProvider->getToken();

        if ($this->isA500Code($token)) {
            return '503: {"error": "No se puede establecer conexión con Twitch en este momento}';
        }

        $api_headers = array('Authorization: Bearer ' . $token);

        return $this->apiClient->makeCurlCall($api_url, $api_headers);
    }

    private function isA500Code(mixed $token): bool
    {
        return isset($token['http_code']) && $token['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
