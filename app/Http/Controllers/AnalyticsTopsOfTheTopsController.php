<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsTopsOfTheTopsRequest;
use Illuminate\Http\Request;
use App\Services\TwitchApi;
use App\Services\Database;
use PDO;

class AnalyticsTopsOfTheTopsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(AnalyticsTopsOfTheTopsRequest $request)
    {
        $database = new Database();
        $client_id = '970almy6xw98ruyojcwqpop0p0o5a2';
        $client_secret = 'yl0nqzjjnadd8wl7zilpr9pzuh979j';
        $twitchApi = new TwitchApi($client_id, $client_secret);
        $results = [];

        $since = $_GET['since'] ?? null;
        $since = $since ?? (10 * 60);

        if (!$database->isLoadedDatabase()) {
            $results = $this->fetchInitialData($twitchApi, $database);
        } elseif ($this->shouldReviewEachTopGame($database, $since)) {
            $this->reviewTopGames($twitchApi, $database, $since);
        }

        $results = $this->fetchTopGamesData($twitchApi, $database);

        return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function fetchInitialData($twitchApi, $database)
    {
        $threeTopGames = $twitchApi->getTopGames();
        $database->insertarTopGames($threeTopGames);
        $results = [];

        foreach ($threeTopGames['data'] as $game) {
            $gameId = $game['id'];
            $gameName = $game['name'];
            $topVideosData = $twitchApi->getTop40VideosDadoUnGameId($gameId);
            $database->insertarVideos($topVideosData, $gameId);

            $stmtAtr = $database->obtenerAtributos($gameId);
            $row = $stmtAtr->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $result = [
                    'game_id' => strval($gameId),
                    'game_name' => $gameName,
                    'user_name' => $row['user_name'],
                    'total_videos' => strval($row['total_videos']),
                    'total_views' => strval($row['total_views']),
                    'most_viewed_title' => $row['most_viewed_title'],
                    'most_viewed_views' => strval($row['most_viewed_views']),
                    'most_viewed_duration' => $row['most_viewed_duration'],
                    'most_viewed_created_at' => $row['most_viewed_created_at']
                ];

                $results[] = $result;
            }
        }

        return $results;
    }

    private function shouldReviewEachTopGame($database, $since)
    {
        $lastUpdateTime = strtotime($database->getOldestUpdateDatetime()['fecha']);
        $currentTime = time();
        $maxTimeDifference = $currentTime - $lastUpdateTime;

        return $maxTimeDifference > $since;
    }

    private function reviewTopGames($twitchApi, $database, $since)
    {
        $threeTopGamesTwitch = $twitchApi->getTopGames();
        $threeTopGamesDB = $database->obtenerIdNombreFechadeJuegos();

        $gamesArray = [];
        $gamesArray[] = $threeTopGamesDB[0]['gamename'];
        $gamesArray[] = $threeTopGamesDB[1]['gamename'];
        $gamesArray[] = $threeTopGamesDB[2]['gamename'];

        foreach ($threeTopGamesTwitch['data'] as $index => $gameTwitch) {
            $fecha = $twitchApi->searchDate($threeTopGamesDB, $gameTwitch['id']);
            if ((in_array($gameTwitch['name'], $gamesArray)) && (time() - strtotime($fecha) > $since)) {
                $database->borrarVideosJuego($gameTwitch['id']);
                $database->insertarVideos($twitchApi->getTop40VideosDadoUnGameId($gameTwitch['id']), $gameTwitch['id']);
                $database->actualizarFechaJuego($gameTwitch['id']);
            } elseif (!(in_array($gameTwitch['name'], $gamesArray)) || !(time() - strtotime($fecha) > $since)) {
                $database->borrarVideosJuego($gameTwitch['id']);
                $database->updateTopGame($index + 1, $gameTwitch['id'], $gameTwitch['name']);
                $database->insertarVideos($twitchApi->getTop40VideosDadoUnGameId($gameTwitch['id']), $gameTwitch['id']);
            }
        }
    }

    private function fetchTopGamesData($twitchApi, $database)
    {
        $threeTopGames = $twitchApi->getTopGames();
        $results = [];

        foreach ($threeTopGames['data'] as $game) {
            $gameId = $game['id'];
            $gameName = $game['name'];

            $stmtAtr = $database->obtenerAtributos($gameId);
            $row = $stmtAtr->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $result = [
                    'game_id' => strval($gameId),
                    'game_name' => $gameName,
                    'user_name' => $row['user_name'],
                    'total_videos' => strval($row['total_videos']),
                    'total_views' => strval($row['total_views']),
                    'most_viewed_title' => $row['most_viewed_title'],
                    'most_viewed_views' => strval($row['most_viewed_views']),
                    'most_viewed_duration' => $row['most_viewed_duration'],
                    'most_viewed_created_at' => $row['most_viewed_created_at']
                ];

                $results[] = $result;
            }
        }

        return $results;
    }
}
