<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Response;

class StreamersDataManager
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;

    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    private const GET_USERS_ERROR_MESSAGE = 'No se pueden devolver usuarios en este momento, inténtalo más tarde';
    private const GET_USERS_URL = 'https://api.twitch.tv/helix/users';

    public function __construct(TokenProvider $tokenProvider, ApiClient $apiClient)
    {
        $this->tokenProvider = $tokenProvider;
        $this->apiClient = $apiClient;
    }

    public function getUserData(string $userId): array
    {
        $twitchToken = $this->tokenProvider->getToken();

        if ($this->requestHas500Code($twitchToken)) {
            throw new Exception(self::GET_TOKEN_ERROR_MESSAGE);
        }

        $apiUrl = self::GET_USERS_URL . "?id=" . urlencode($userId);
        $apiHeaders = ['Authorization: Bearer ' . $twitchToken];

        $userData = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

        if ($this->requestHas500Code($userData)) {
            throw new Exception(self::GET_USERS_ERROR_MESSAGE);
        }

        return $userData['response'];
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
