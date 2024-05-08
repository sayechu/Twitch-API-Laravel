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

    public function getStreamsData(): array
    {
        $api_url = 'https://api.twitch.tv/helix/streams';
        $api_headers = array('Authorization: Bearer ' . $this->tokenProvider->getToken());

        $responseCurlCall = $this->apiClient->makeCurlCall($api_url, $api_headers);

        if ($this->isA500Code($responseCurlCall['http_code'])) {
            return '503: {"error": "No se pueden devolver streams en este momento, inténtalo más tarde"}';
        }

        return json_decode($responseCurlCall['response'], true)['data'];
    }

    private function isA500Code(int $http_code): bool
    {
        return $http_code === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
