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

    public function getUserData(string $userId): array | string
    {
        $api_url = "https://api.twitch.tv/helix/users?id=" . urlencode($userId);
        $tokenResponse = $this->tokenProvider->getToken();

        if ($this->isA500Code($tokenResponse)) {
            return '503: {"error": "No se puede establecer conexión con Twitch en este momento}';
        }

        $api_headers = array('Authorization: Bearer ' . $tokenResponse);

        $userData = $this->apiClient->makeCurlCall($api_url, $api_headers);

        if ($this->isA500Code($userData)) {
            return '503: {"error": "No se pueden devolver usuarios en este momento, inténtalo más tarde"}';
        }

        return json_decode($userData['response'], true);
    }

    private function isA500Code(mixed $token): bool
    {
        return isset($token['http_code']) && $token['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
