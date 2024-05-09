<?php

namespace App\Services;

use Illuminate\Http\Response;

class UserDataManager
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;

    public function __construct(TokenProvider $tokenProvider, ApiClient $apiClient)
    {
        $this->tokenProvider = $tokenProvider;
        $this->apiClient = $apiClient;
    }

    public function getUserData(string $userId): string|array
    {
        $twitchToken = $this->tokenProvider->getToken();

        if ($this->requestHas500Code($twitchToken)) {
            return '503: {"error": "No se puede establecer conexión con Twitch en este momento}';
        }

        $apiUrl = "https://api.twitch.tv/helix/users?id=" . urlencode($userId);

        $apiHeaders = ['Authorization: Bearer ' . $twitchToken];

        $userData = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

        if ($this->requestHas500Code($userData)) {
            return '503: {"error": "No se pueden devolver usuarios en este momento, inténtalo más tarde"}';
        }

        return json_decode($userData['response'], true);
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
