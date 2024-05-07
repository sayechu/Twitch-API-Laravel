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
        $resultCurlCall = $this->apiClient->makeCurlCall($api_url, $api_headers);

        if ($this->isA500Code($resultCurlCall['http_code'])) {
            return '503: {"error": "No se pueden devolver usuarios en este momento,
            inténtalo más tarde"}';
        }

        return json_decode($resultCurlCall['response'], true);
    }

    private function isA500Code(int $http_code): bool
    {
        return $http_code == 500;
    }
}
