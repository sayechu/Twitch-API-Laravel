<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Response;

class TopsOfTheTopsManager
{
    private TokenProvider $tokenProvider;
    private TopsOfTheTopsDataProvider $topsOfTopsDataProv;
    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    private const GET_TOP_GAMES_ERROR_MESSAGE = 'No se pueden devolver top games en este momento, inténtalo más tarde';

    public function __construct(TokenProvider $tokenProvider, TopsOfTheTopsDataProvider $topsOfTopsDataProv)
    {
        $this->tokenProvider = $tokenProvider;
        $this->topsOfTopsDataProv = $topsOfTopsDataProv;
    }

    public function getTopsOfTheTops(int $since): array
    {
        $results = [];
        $twitchTokenResponse = $this->tokenProvider->getToken();

        if ($this->requestHas500Code($twitchTokenResponse)) {
            throw new Exception(self::GET_TOKEN_ERROR_MESSAGE);
        }

        $apiHeaders = ['Authorization: Bearer ' . $twitchTokenResponse];

        $topThreeGames = $this->topsOfTopsDataProv->getTopThreeGames($apiHeaders);

        if ($this->requestHas500Code($topThreeGames)) {
            throw new Exception(self::GET_TOP_GAMES_ERROR_MESSAGE);
        }

        if (!$this->topsOfTopsDataProv->isThereAnyStoredGame()) {
            $this->dbClient->addTopThreeGamesToDB($topThreeGames);
            foreach ($topThreeGames as $topGame) {
                $topFourtyVideos = $this->topsOfTopsDataProv->getTopFourtyVideos($topGame['id'], $apiHeaders);
                $this->dbClient->addVideosToDB($topFourtyVideos, $topGame['id']);
                $results = $this->getTopsOfTheTopsAttributes($topGame, $results);
            }
            return $results;
        }
        if ($this->shouldReviewEachTopGame($since)) {
            $this->reviewEachTopGame($topThreeGames, $since, $apiHeaders);
        }

        return $this->fetchTopGamesData($topThreeGames);
    }

    private function shouldReviewEachTopGame($since): bool
    {
        $lastUpdateTime = strtotime($this->dbClient->getOldestUpdateDatetime()['fecha']);
        $currentTime = time();
        $maxTimeDifference = $currentTime - $lastUpdateTime;

        return $maxTimeDifference > $since;
    }

    private function reviewEachTopGame(array $topThreeGames, int $since, array $api_headers): void
    {
        $gamesArray = [];

        $topThreeGamesInDB = $this->dbClient->getTopThreeGames();

        $gamesArray[] = $topThreeGamesInDB[0]['gameName'];
        $gamesArray[] = $topThreeGamesInDB[1]['gameName'];
        $gamesArray[] = $topThreeGamesInDB[2]['gameName'];

        foreach ($topThreeGames as $index => $twitchGame) {
            $date = $this->searchGameDate($topThreeGamesInDB, $twitchGame['id']);
            if ((in_array($twitchGame['name'], $gamesArray)) && (time() - strtotime($date) > $since)) {
                $this->dbClient->deleteVideosOfAGivenGame($twitchGame['id']);
                $this->dbClient->addVideosToDB(
                    $this->topsOfTopsDataProv->getTopFourtyVideos($twitchGame['id'], $api_headers),
                    $twitchGame['id']
                );
                $this->dbClient->updateDatetime($twitchGame['id']);
            } elseif (!(in_array($twitchGame['name'], $gamesArray)) || !(time() - strtotime($date) > $since)) {
                $gameId = $this->dbClient->getGameIdAtPosition($index + 1);
                $this->dbClient->deleteVideosOfAGivenGame($gameId);
                $this->dbClient->updateTopGame($index + 1, $twitchGame['id'], $twitchGame['name']);
                $this->dbClient->addVideosToDB(
                    $this->topsOfTopsDataProv->getTopFourtyVideos($twitchGame['id'], $api_headers),
                    $twitchGame['id']
                );
            }
        }
    }

    private function fetchTopGamesData(array $topThreeGames): array
    {
        $results = [];

        foreach ($topThreeGames as $topGame) {
            $results = $this->getTopsOfTheTopsAttributes($topGame, $results);
        }

        return $results;
    }

    private function getTopsOfTheTopsAttributes(mixed $topGame, array $results): array
    {
        $topsOfTheTopsAttr = $this->dbClient->getTopsOfTheTopsAttributes($topGame['id']);
        $resultTopsOfTheTops = [
            'game_id' => strval($topGame['id']),
            'game_name' => $topGame['name'],
            'user_name' => $topsOfTheTopsAttr['user_name'],
            'total_videos' => strval($topsOfTheTopsAttr['total_videos']),
            'total_views' => strval($topsOfTheTopsAttr['total_views']),
            'most_viewed_title' => $topsOfTheTopsAttr['most_viewed_title'],
            'most_viewed_views' => strval($topsOfTheTopsAttr['most_viewed_views']),
            'most_viewed_duration' => $topsOfTheTopsAttr['most_viewed_duration'],
            'most_viewed_created_at' => $topsOfTheTopsAttr['most_viewed_created_at']
        ];

        $results[] = $resultTopsOfTheTops;
        return $results;
    }

    private function searchGameDate(array $topThreeGamesInDB, string $gameId): string
    {
        foreach ($topThreeGamesInDB as $game) {
            if ($game['gameId'] == $gameId) {
                return $game['fecha'];
            }
        }
        return '';
    }

    private function requestHas500Code(mixed $requestResponse): bool
    {
        return isset($requestResponse['http_code']) &&
            $requestResponse['http_code'] === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
