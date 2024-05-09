<?php

namespace App\Services;

class UserDataProvider
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getUserData(string $api_url, array $api_headers): array
    {
        return $this->apiClient->makeCurlCall($api_url, $api_headers);
    }
}
