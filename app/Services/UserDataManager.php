<?php

namespace App\Services;

use Illuminate\Http\Response;

class UserDataManager
{
    private TokenProvider $tokenProvider;
    private UserDataProvider $userDataProvider;

    private const ERROR_GET_TOKEN_FAILED = 'No se puede establecer conexión con Twitch en este momento';
    private const ERROR_GET_USERS_FAILED = 'No se pueden devolver usuarios en este momento, inténtalo más tarde';


    public function __construct(TokenProvider $tokenProvider, UserDataProvider $userDataProvider)
    {
        $this->tokenProvider = $tokenProvider;
        $this->userDataProvider = $userDataProvider;
    }

    public function getUserData(string $userId): array
    {
        $twitchToken = $this->tokenProvider->getToken();

        if ($this->requestHas500Code($twitchToken)) {
            return ['error' => self::ERROR_GET_TOKEN_FAILED];
        }

        $userData = $this->userDataProvider->getUserData($userId, $twitchToken);

        if ($this->requestHas500Code($userData)) {
            return ['error' => self::ERROR_GET_USERS_FAILED];
        }

        return json_decode($userData['response'], true);
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
