<?php

namespace App\Services;

use Illuminate\Http\Response;

class UserDataProvider
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;

    public function __construct(ApiClient $apiClient, DBClient $databaseClient)
    {
        $this->databaseClient = $databaseClient;
        $this->apiClient = $apiClient;
    }

    public function getUserData(string $userId, string $twitchToken): array
    {
        if ($this->databaseClient->isUserStoredInDatabase($userId)) {
            return $this->databaseClient->getUserFromDatabase($userId);
        }

        $apiUrl = "https://api.twitch.tv/helix/users?id=" . urlencode($userId);
        $apiHeaders = ['Authorization: Bearer ' . $twitchToken];

        $userDataResponse = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

        if ($this->requestHas500Code($userDataResponse)) {
            return $userDataResponse;
        }

        $this->databaseClient->addUserToDatabase($userDataResponse);
        return $userDataResponse;
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
