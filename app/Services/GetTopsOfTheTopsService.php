<?php

namespace App\Services;

class GetTopsOfTheTopsService
{
    private TopsOfTheTopsManager $topsOfTheTopsManager;

    public function __construct(TopsOfTheTopsManager $topsOfTheTopsManager)
    {
        $this->topsOfTheTopsManager = $topsOfTheTopsManager;
    }

    public function execute(int $since): array
    {
        $result = [];
        $topVideosOfTopGames = $this->topsOfTheTopsManager->getTopVideosOfTopGames($since);

        foreach ($topVideosOfTopGames as $topGameVideos) {
            $mostViewedVideo = null;

            foreach ($topGameVideos as $video) {
                if ($mostViewedVideo === null || $video['view_count'] > $mostViewedVideo['view_count']) {
                    $mostViewedVideo = $video;
                }
            }

            $result[] = [
                'game_id' => $mostViewedVideo['game_id'],
                'game_name' => $mostViewedVideo['game_name'],
                'user_name' => $mostViewedVideo['user_name'],
                'total_videos' => $this->getTotalVideos($mostViewedVideo['user_id'], $topGameVideos),
                'total_views' => $this->getTotalViews($mostViewedVideo['user_id'], $topGameVideos),
                'most_viewed_title' => $mostViewedVideo['title'],
                'most_viewed_views' => $mostViewedVideo['view_count'],
                'most_viewed_duration' => $mostViewedVideo['duration'],
                'most_viewed_created_at' => $mostViewedVideo['created_at'],
            ];
        }

        return $result;
    }

    private function getTotalVideos(mixed $user_id, mixed $topGameVideos): string
    {
        $counter = 0;
        foreach ($topGameVideos as $video) {
            if ($video['user_id'] === $user_id) {
                $counter += 1;
            }
        }
        return strval($counter);
    }

    private function getTotalViews(mixed $user_id, mixed $topGameVideos): string
    {
        $counter = 0;
        foreach ($topGameVideos as $video) {
            if ($video['user_id'] === $user_id) {
                $counter += $video['view_count'];
            }
        }
        return strval($counter);
    }
}
