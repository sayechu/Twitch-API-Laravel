<?php

namespace App\Services;

use Illuminate\Http\Response;

class TokenProvider
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;

    public function __construct(ApiClient $apiClient, DBClient $databaseClient)
    {
        $this->apiClient = $apiClient;
        $this->databaseClient = $databaseClient;
    }

    public function getToken(): string|array
    {
        if ($this->databaseClient->isTokenStoredInDatabase()) {
            return $this->databaseClient->getToken();
        }

        $twitchTokenResponse = $this->apiClient->getToken();

        if ($this->requestHas500Code($twitchTokenResponse)) {
            return $twitchTokenResponse;
        }

        $this->storeTokenInDatabase($twitchTokenResponse);
        return $this->extractToken($twitchTokenResponse['response']);
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function storeTokenInDatabase(array $twitchTokenResponse): void
    {
        $twitchToken = $this->extractToken($twitchTokenResponse['response']);
        $this->databaseClient->storeToken($twitchToken);
    }

    private function extractToken(string $responseArray): string
    {
        return json_decode($responseArray, true)['access_token'];
    }
}
