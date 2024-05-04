<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsTopsOfTheTopsRequest;
use App\Services\GetTopsOfTheTopsService;
use App\Services\VerificateManagement;
use App\Services\DeleteTableManagement;
use App\Services\InsertTableManagement;
use App\Services\SelectTableManagement;
use App\Services\UpdateTableManagement;
use Illuminate\Http\JsonResponse;
use PDO;

class AnalyticsTopsOfTheTopsController extends Controller
{
    private GetTopsOfTheTopsService $getTopsOfTheTopsService;
    public function __construct(GetTopsOfTheTopsService $getTopsOfTheTopsService)
    {
        $this->getTopsOfTheTopsService = $getTopsOfTheTopsService;
    }
    public function __invoke(AnalyticsTopsOfTheTopsRequest $request): JsonResponse
    {
        $dbInstanceVerificate = new VerificateManagement();
        $dbInstanceDelete = new DeleteTableManagement();
        $dbInstanceInsert = new InsertTableManagement();
        $dbInstanceSelect = new SelectTableManagement();
        $dbInstanceUpdate = new UpdateTableManagement();
        $results = [];

        $since = $_GET['since'] ?? null;
        $since = $since ?? (10 * 60);

        if (!$dbInstanceVerificate->isLoadedDatabase()) {
            $results = $this->fetchInitialData($dbInstanceInsert, $dbInstanceSelect);
        } elseif ($this->shouldReviewEachTopGame($dbInstanceSelect, $since)) {
            $this->reviewTopGames(
                $dbInstanceSelect,
                $dbInstanceDelete,
                $dbInstanceInsert,
                $dbInstanceUpdate,
                $since
            );
        }

        $results = $this->fetchTopGamesData($dbInstanceSelect);

        return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function fetchInitialData($dbInstanceInsert, $dbInstanceSelect): array
    {
        $threeTopGames = $this->getTopsOfTheTopsService->getTopGames();
        $dbInstanceInsert->insertarTopGames($threeTopGames);
        $results = [];

        foreach ($threeTopGames['data'] as $game) {
            $gameId = $game['id'];
            $gameName = $game['name'];
            $topVideosData = $this->getTopsOfTheTopsService->getTop40VideosDadoUnGameId($gameId);
            $dbInstanceInsert->insertarVideos($topVideosData, $gameId);

            $results = $this->getRequiredTypesAttributesResult($dbInstanceSelect, $gameId, $gameName, $results);
        }

        return $results;
    }

    private function shouldReviewEachTopGame($dbInstanceSelect, $since): bool
    {
        $lastUpdateTime = strtotime($dbInstanceSelect->getOldestUpdateDatetime()['fecha']);
        $currentTime = time();
        $maxTimeDifference = $currentTime - $lastUpdateTime;

        return $maxTimeDifference > $since;
    }

    private function reviewTopGames(
        $dbInstanceSelect,
        $dbInstanceDelete,
        $dbInstanceInsert,
        $dbInstanceUpdate,
        $since
    ): void
    {
        $threeTopGamesTwitch = $this->getTopsOfTheTopsService->getTopGames();
        $threeTopGamesDB = $dbInstanceSelect->obtenerIdNombreFechadeJuegos();

        $gamesArray = [];
        $gamesArray[] = $threeTopGamesDB[0]['gameName'];
        $gamesArray[] = $threeTopGamesDB[1]['gameName'];
        $gamesArray[] = $threeTopGamesDB[2]['gameName'];

        foreach ($threeTopGamesTwitch['data'] as $index => $gameTwitch) {
            $fecha = $this->searchDate($threeTopGamesDB, $gameTwitch['id']);
            if ((in_array($gameTwitch['name'], $gamesArray)) && (time() - strtotime($fecha) > $since)) {
                $dbInstanceDelete->borrarVideosJuego($gameTwitch['id']);
                $dbInstanceInsert->insertarVideos(
                    $this->getTopsOfTheTopsService->getTop40VideosDadoUnGameId($gameTwitch['id']),
                    $gameTwitch['id']
                );
                $dbInstanceUpdate->actualizarFechaJuego($gameTwitch['id']);
            } elseif (!(in_array($gameTwitch['name'], $gamesArray)) || !(time() - strtotime($fecha) > $since)) {
                $gameId = $dbInstanceSelect->obtenerGameIdporPosicion($index + 1);
                $dbInstanceDelete->borrarVideosJuego($gameId[0]['gameId']);
                $dbInstanceUpdate->updateTopGame($index + 1, $gameTwitch['id'], $gameTwitch['name']);
                $dbInstanceInsert->insertarVideos(
                    $this->getTopsOfTheTopsService->getTop40VideosDadoUnGameId($gameTwitch['id']),
                    $gameTwitch['id']
                );
            }
        }
    }

    private function fetchTopGamesData($dbInstanceSelect): array
    {
        $threeTopGames = $this->getTopsOfTheTopsService->getTopGames();
        $results = [];

        foreach ($threeTopGames['data'] as $game) {
            $gameId = $game['id'];
            $gameName = $game['name'];

            $results = $this->getRequiredTypesAttributesResult($dbInstanceSelect, $gameId, $gameName, $results);
        }

        return $results;
    }

    public function searchDate($topGamesList, $idGame)
    {
        foreach ($topGamesList as $game) {
            if ($game['gameId'] == $idGame) {
                return $game['fecha'];
            }
        }
        return null;
    }

    private function getRequiredTypesAttributesResult($dbInstanceSelect, mixed $gameId, mixed $gameName, array $results): array
    {
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
        return $results;
    }
}
