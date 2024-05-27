<?php

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;
use Illuminate\Http\Response;

class FollowStreamerManager
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;
    private const GET_STREAMER_DATA_URL = 'https://api.twitch.tv/helix/users';
    private const GET_TOKEN_ERROR_MESSAGE = 'Acceso denegado debido a permisos insuficientes.';


    public function __construct(TokenProvider $tokenProvider, ApiClient $apiClient)
    {
        $this->tokenProvider = $tokenProvider;
        $this->apiClient = $apiClient;
    }

    public function getFollowMessage($userId, $streamerId): string
    {
        $twitchToken = $this->tokenProvider->getToken();

        if ($this->requestHas500Code($twitchToken)) {
            throw new ForbiddenException(self::GET_TOKEN_ERROR_MESSAGE);
        }

        if (!$this->checkIfStreamerExists($streamerId, $twitchToken)) {
            return "Streamer no existe";
        }
        return "Streamer existe";
    }

    private function checkIfStreamerExists($streamerId, $twitchToken)
    {
        $apiUrl = self::GET_STREAMER_DATA_URL . '?id=' . urlencode($streamerId);
        $apiHeaders = ['Authorization: Bearer ' . $twitchToken];

        $streamerResponse = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

        return $streamerResponse['http_code'] === 200;
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
