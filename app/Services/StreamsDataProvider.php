<?php

namespace App\Services;

class StreamsDataProvider
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function streamsUserData($api_headers): array
    {
        $api_url = 'https://api.twitch.tv/helix/streams';

        $responseCurlCall = $this->apiClient->makeCurlCall($api_url, $api_headers);

        if ($this->isA500Code($responseCurlCall['http_code'])) {
            return '503: {"error": "No se pueden devolver streams en este momento, inténtalo más tarde"}';
        }

        return json_decode($responseCurlCall['response'], true)['data'];
    }

    private function isA500Code($http_code): bool
    {
        return $http_code == 500;
    }
}
