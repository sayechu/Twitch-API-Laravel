<?php

namespace App\Services;

use Illuminate\Http\Response;

class UserDataManager
{
    private TokenProvider $tokenProvider;
    private UserDataProvider $userDataProvider;

    public function __construct(TokenProvider $tokenProvider, UserDataProvider $userDataProvider)
    {
        $this->tokenProvider = $tokenProvider;
        $this->userDataProvider = $userDataProvider;
    }

    public function getUserData(string $userId): array
    {
        $api_url = "https://api.twitch.tv/helix/users?id=" . urlencode($userId);
        $api_headers = array('Authorization: Bearer ' . $this->tokenProvider->getToken());

        $userData = $this->userDataProvider->getUserData($api_url, $api_headers);

        if ($this->isA500Code($userData['http_code'])) {
            return '503: {"error": "No se pueden devolver usuarios en este momento,
            inténtalo más tarde"}';
        }

        return json_decode($userData['response'], true);
    }

    private function isA500Code(int $http_code): bool
    {
        return $http_code === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
