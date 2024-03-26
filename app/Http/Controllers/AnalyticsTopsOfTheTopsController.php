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
        $client_id = '970almy6xw98ruyojcwqpop0p0o5a2';
        $client_secret = 'yl0nqzjjnadd8wl7zilpr9pzuh979j';

        $db = new Database();
        $twitchApi = new TwitchApi($client_id, $client_secret);

        $since = $request->input('since') ?? null;
        $lastUpdate = $db->obtenerFechaUltimaInsercion();
        $tenMinutesInSeconds = 10 * 60;
        $shouldUpdate = false;

        if ($lastUpdate) {
            $lastUpdateTime = strtotime($lastUpdate['fecha']);
            $currentTime = time();

            $since = $since ?? $tenMinutesInSeconds;

            if (($currentTime - $lastUpdateTime) > $since) {
                $shouldUpdate = true;
            }
        } else {
            $shouldUpdate = true;
        }

        if ($shouldUpdate) {
            $db->clearTablas();

            $topGamesData = $twitchApi->getTopGames();
            $db->insertarTopGames($topGamesData);

            foreach ($topGamesData['data'] as $game) {
                $gameId = $game['id'];
                $topVideosData = $twitchApi->getTop40VideosDadoUnGameId($gameId);
                $db->insertarVideos($topVideosData, $gameId);
            }
        }

        $stmt = $db->obtenerNombreJuegoEId();
        $results = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $gameId = $fila['gameId'];
            $gameName = $fila['gameName'];

            $stmtAtr = $db->obtenerAtributos($gameId);
            if ($row = $stmtAtr->fetch(PDO::FETCH_ASSOC)) {
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
            } else {
                $result = [
                    'error' => 'No se encontraron datos para el juego con ID ' . $gameId
                ];
                $results[] = $result;
            }
        }

        return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
