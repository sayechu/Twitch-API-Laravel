<?php

namespace App\Services;

use Illuminate\Http\Response;
use Exception;

class TopsOfTheTopsManager
{
    private TokenProvider $tokenProvider;
    private TopGamesProvider $topGamesProvider;
    private TopVideosProvider $topVideosProvider;
    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    private const GET_TOP_GAMES_ERROR_MESSAGE = 'No se pueden devolver top games en este momento, inténtalo más tarde';
    private const GET_TOP_VIDEOS_ERROR_MESSAGE =
        'No se pueden devolver top 40 videos en este momento, inténtalo más tarde';

    public function __construct(
        TokenProvider $tokenProvider,
        TopGamesProvider $topGamesProvider,
        TopVideosProvider $topVideosProvider
    ) {
        $this->tokenProvider = $tokenProvider;
        $this->topGamesProvider = $topGamesProvider;
        $this->topVideosProvider = $topVideosProvider;
    }

    public function getTopVideosOfTopGames(int $since): array
    {
        $topVideos = [];
        $twitchTokenResponse = $this->tokenProvider->getToken();

        if ($this->requestHas500Code($twitchTokenResponse)) {
            throw new Exception(self::GET_TOKEN_ERROR_MESSAGE);
        }

        $apiHeaders = ['Authorization: Bearer ' . $twitchTokenResponse];
        $topGamesResponse = $this->topGamesProvider->getTopThreeGames($apiHeaders);

        if ($this->requestHas500Code($topGamesResponse)) {
            throw new Exception(self::GET_TOP_GAMES_ERROR_MESSAGE);
        }

        foreach ($topGamesResponse as $topGame) {
            $responseTopVideos = $this->topVideosProvider->getTopFourtyVideos(
                $topGame['id'],
                $topGame['name'],
                $since,
                $apiHeaders
            );

            if ($this->requestHas500Code($responseTopVideos)) {
                throw new Exception(self::GET_TOP_VIDEOS_ERROR_MESSAGE);
            }

            $topVideos[] = $responseTopVideos;
        }

        return $topVideos;
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
