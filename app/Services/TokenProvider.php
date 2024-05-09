<?php

namespace App\Services;

class TokenProvider
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;

    public function __construct(ApiClient $apiClient, DBClient $databaseClient)
    {
        $this->apiClient = $apiClient;
        $this->databaseClient = $databaseClient;
    }

    public function getToken(): array | string
    {
        if ($this->thereIsTokenStored()) {
            return $this->databaseClient->getToken();
        }

        $twitchArrayToken = $this->apiClient->getToken();

        if ($this->isA500Code($twitchArrayToken['http_code'])) {
            return $twitchArrayToken;
        }

        $twitchToken = $this->getTokenFromArray($twitchArrayToken['response']);
        $this->databaseClient->addToken($twitchToken);

        return $twitchArrayToken;
    }

    private function thereIsTokenStored(): bool
    {
        return $this->databaseClient->isTokenStoredInDatabase();
    }

    private function isA500Code(int $http_code): bool
    {
        return $http_code == 500;
    }

    private function getTokenFromArray(string $responseArray): string
    {
        return json_decode($responseArray, true)['access_token'];
    }
}
