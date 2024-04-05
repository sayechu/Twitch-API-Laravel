<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsTopsOfTheTopsRequest;
use Illuminate\Http\Request;
use App\Services\TwitchApi;
use App\Services\Database;
use App\Services\DeleteTableManagement;
use App\Services\InsertTableManagement;
use App\Services\SelectTableManagement;
use App\Services\TableManagement;
use App\Services\TokenManagement;
use App\Services\UpdateTableManagement;
use PDO;

class AnalyticsTopsOfTheTopsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(AnalyticsTopsOfTheTopsRequest $request)
    {
        $database = new Database();
        $dbInstanceDelete = new DeleteTableManagement();
        $dbInstanceInsert = new InsertTableManagement();
        $dbInstanceSelect = new SelectTableManagement();
        $dbInstanceUpdate = new UpdateTableManagement();
        $dbInstanceToken = new TokenManagement();
        $dbInstanceTable = new TableManagement();
        $client_id = '970almy6xw98ruyojcwqpop0p0o5a2';
        $client_secret = 'yl0nqzjjnadd8wl7zilpr9pzuh979j';
        $twitchApi = new TwitchApi($client_id, $client_secret);
        $results = [];

        $since = $_GET['since'] ?? null;
        $since = $since ?? (10 * 60);

        if (!$database->isLoadedDatabase()) {
            $results = $this->fetchInitialData($twitchApi, $dbInstanceInsert, $dbInstanceSelect);
        } elseif ($this->shouldReviewEachTopGame($dbInstanceSelect, $since)) {
            $this->reviewTopGames($twitchApi, $dbInstanceSelect, $dbInstanceDelete, $dbInstanceInsert, $dbInstanceUpdate, $since);
        }

        $results = $this->fetchTopGamesData($twitchApi, $dbInstanceSelect);

        return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function fetchInitialData($twitchApi, $dbInstanceInsert, $dbInstanceSelect)
    {
        $threeTopGames = $twitchApi->getTopGames();
        $dbInstanceInsert->insertarTopGames($threeTopGames);
        $results = [];

        foreach ($threeTopGames['data'] as $game) {
            $gameId = $game['id'];
            $gameName = $game['name'];
            $topVideosData = $twitchApi->getTop40VideosDadoUnGameId($gameId);
            $dbInstanceInsert->insertarVideos($topVideosData, $gameId);

            $stmtAtr = $dbInstanceSelect->obtenerAtributos($gameId);
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

    private function shouldReviewEachTopGame($dbInstanceSelect, $since)
    {
        $lastUpdateTime = strtotime($dbInstanceSelect->getOldestUpdateDatetime()['fecha']);
        $currentTime = time();
        $maxTimeDifference = $currentTime - $lastUpdateTime;

        return $maxTimeDifference > $since;
    }

    private function reviewTopGames($twitchApi, $dbInstanceSelect, $dbInstanceDelete, $dbInstanceInsert, $dbInstanceUpdate, $since)
    {
        $threeTopGamesTwitch = $twitchApi->getTopGames();
        $threeTopGamesDB = $dbInstanceSelect->obtenerIdNombreFechadeJuegos();

        $gamesArray = [];
        $gamesArray[] = $threeTopGamesDB[0]['gameName'];
        $gamesArray[] = $threeTopGamesDB[1]['gameName'];
        $gamesArray[] = $threeTopGamesDB[2]['gameName'];

        foreach ($threeTopGamesTwitch['data'] as $index => $gameTwitch) {
            $fecha = $twitchApi->searchDate($threeTopGamesDB, $gameTwitch['id']);
            if ((in_array($gameTwitch['name'], $gamesArray)) && (time() - strtotime($fecha) > $since)) {
                $dbInstanceDelete->borrarVideosJuego($gameTwitch['id']);
                $dbInstanceInsert->insertarVideos($twitchApi->getTop40VideosDadoUnGameId($gameTwitch['id']), $gameTwitch['id']);
                $dbInstanceUpdate->actualizarFechaJuego($gameTwitch['id']);
            } elseif (!(in_array($gameTwitch['name'], $gamesArray)) || !(time() - strtotime($fecha) > $since)) {
                // no tiene sentido borrar videos de un juego que no esta en la bd
                // yo creo que a partir de la posicion, tienes que obtener el id del juego
                // cuando obtienes ese id, le pasas el id a borrarVideosJuego()
                // el resto es igual
                $dbInstanceDelete->borrarVideosJuego($gameTwitch['id']);
                $dbInstanceUpdate->updateTopGame($index + 1, $gameTwitch['id'], $gameTwitch['name']);
                $dbInstanceInsert->insertarVideos($twitchApi->getTop40VideosDadoUnGameId($gameTwitch['id']), $gameTwitch['id']);
            }
        }
    }

    private function fetchTopGamesData($twitchApi, $dbInstanceSelect)
    {
        $threeTopGames = $twitchApi->getTopGames();
        $results = [];

        foreach ($threeTopGames['data'] as $game) {
            $gameId = $game['id'];
            $gameName = $game['name'];

            $stmtAtr = $dbInstanceSelect->obtenerAtributos($gameId);
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
