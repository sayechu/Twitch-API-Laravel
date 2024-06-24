<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use Illuminate\Http\Response;

class FollowStreamerManager
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;
    private DBClient $databaseClient;
    private const GET_STREAMER_DATA_URL = 'https://api.twitch.tv/helix/users';
    private const GET_TOKEN_ERROR_MESSAGE = 'Acceso denegado debido a permisos insuficientes';
    private const GET_STREAMER_ERROR_MESSAGE = 'Token de autenticación no proporcionado o inválido';
    private const CONFLICT_EXCEPTION_MESSAGE = 'El usuario ya está siguiendo al streamer';

    public function __construct(TokenProvider $tokenProvider, ApiClient $apiClient, DBClient $databaseClient)
    {
        $this->tokenProvider = $tokenProvider;
        $this->apiClient = $apiClient;
        $this->databaseClient = $databaseClient;
    }

    public function getFollowMessage($username, $streamerId): array
    {
        if ($this->databaseClient->userFollowsStreamer($username, $streamerId)) {
            throw new ConflictException(self::CONFLICT_EXCEPTION_MESSAGE);
        }

        $twitchToken = $this->tokenProvider->getToken();
        if ($this->requestHas500Code($twitchToken)) {
            throw new ForbiddenException(self::GET_TOKEN_ERROR_MESSAGE);
        }

        $streamerData = $this->checkIfStreamerExists($streamerId, $twitchToken);
        if (empty($streamerData) || !$this->databaseClient->checkIfUsernameExists($username)) {
            throw new NotFoundException("El usuario (" . $username . ") o el streamer ("
                . $streamerId . ") especificado no existe en la API");
        }

        $this->databaseClient->addUserFollowsStreamer($username, $streamerId);
        return [
            "message" => "Ahora sigues a " . $streamerId
        ];
    }

    private function checkIfStreamerExists($streamerId, $twitchToken): array
    {
        $apiUrl = self::GET_STREAMER_DATA_URL . '?id=' . urlencode($streamerId);
        $apiHeaders = ['Authorization: Bearer ' . $twitchToken];

        $streamerResponse = $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);

        if ($this->requestHas401Code($streamerResponse)) {
            throw new UnauthorizedException(self::GET_STREAMER_ERROR_MESSAGE);
        }

        return $streamerResponse['response']['data'];
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function requestHas401Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_UNAUTHORIZED;
    }
}
