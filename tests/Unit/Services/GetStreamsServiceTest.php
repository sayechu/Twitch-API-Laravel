<?php

namespace Tests\Unit\Services;

use App\Services\GetStreamsService;
use App\Services\StreamsDataManager;
use Mockery;
use Tests\TestCase;

class GetStreamsServiceTest extends TestCase
{
    /**
     * @test
     */
    public function get_streams_test(): void
    {
        $streamsDataManager = Mockery::mock(StreamsDataManager::class);
        $this->app
            ->when(GetStreamsService::class)
            ->needs(StreamsDataManager::class)
            ->give(fn() => $streamsDataManager);

        $getStreamsService = new GetStreamsService($streamsDataManager);

        $getStreamsExpectedResponse = [
            'response' => json_encode([
                'data' => [
                    [
                        'id' => '40627613557',
                        'user_id' => '92038375',
                        'user_login' => 'caedrel',
                        'user_name' => 'User Name 1',
                        'game_id' => '21779',
                        'game_name' => 'League of Legends',
                        'type' => 'live',
                        'title' => 'Stream Title 1',
                        'viewer_count' => 46181,
                        'started_at' => '2024-05-08T07:35:07Z',
                        'language' => 'en',
                        'thumbnail_url' => 'https://static-cdn.jtvnw.net/previews-ttv/live_user_caedrel-{width}x{height}.jpg',
                        'tag_ids' => [],
                        'tags' => ['xdd', 'Washed', 'degen', 'English', 'adhd', 'vtuber', 'Ratking', 'LPL', 'LCK', 'LEC'],
                        'is_mature' => false
                    ],
                    [
                        'id' => '40627613557',
                        'user_id' => '92038375',
                        'user_login' => 'caedrel',
                        'user_name' => 'User Name 2',
                        'game_id' => '21779',
                        'game_name' => 'League of Legends',
                        'type' => 'live',
                        'title' => 'Stream Title 2',
                        'viewer_count' => 46181,
                        'started_at' => '2024-05-08T07:35:07Z',
                        'language' => 'en',
                        'thumbnail_url' => 'https://static-cdn.jtvnw.net/previews-ttv/live_user_caedrel-{width}x{height}.jpg',
                        'tag_ids' => [],
                        'tags' => ['xdd', 'Washed', 'degen', 'English', 'adhd', 'vtuber', 'Ratking', 'LPL', 'LCK', 'LEC'],
                        'is_mature' => false
                    ]
                ]
            ]),
            'http_code' => 200
        ];
        $expectedFilteredStreams = [
            [
                'title' => 'Stream Title 1',
                'user_name' => 'User Name 1'
            ],
            [
                'title' => 'Stream Title 2',
                'user_name' => 'User Name 2'
            ]
        ];

        $streamsDataManager
            ->expects('getStreamsData')
            ->once()
            ->andReturn($getStreamsExpectedResponse);

        $returnedStreams = $getStreamsService->execute();

        $this->assertEquals($expectedFilteredStreams, $returnedStreams);
    }

    /**
     * @test
     */
    public function gets_streams_token_failure(): void
    {
        $streamsDataManager = Mockery::mock(StreamsDataManager::class);

        $getStreamsExpectedResponse = '503: {"error": "No se puede establecer conexión con Twitch en este momento}';
        $streamsDataManager
            ->expects('getStreamsData')
            ->once()
            ->andReturn($getStreamsExpectedResponse);


        $getStreamsService = new GetStreamsService($streamsDataManager);
        $returnedStreams = $getStreamsService->execute();

        $this->assertEquals('503: {"error": "No se puede establecer conexión con Twitch en este momento}', $returnedStreams);
    }

    /**
     * @test
     */
    public function gets_streams_token_correct_curl_failure(): void
    {
        $streamsDataManager = Mockery::mock(StreamsDataManager::class);

        $getStreamsExpectedResponse = [
            'response' => json_encode([
                'data' => [
                    [
                        'id' => '40627613557',
                        'user_id' => '92038375',
                        'user_login' => 'caedrel',
                        'user_name' => 'User Name 1',
                        'game_id' => '21779',
                        'game_name' => 'League of Legends',
                        'type' => 'live',
                        'title' => 'Stream Title 1',
                        'viewer_count' => 46181,
                        'started_at' => '2024-05-08T07:35:07Z',
                        'language' => 'en',
                        'thumbnail_url' => 'https://static-cdn.jtvnw.net/previews-ttv/live_user_caedrel-{width}x{height}.jpg',
                        'tag_ids' => [],
                        'tags' => ['xdd', 'Washed', 'degen', 'English', 'adhd', 'vtuber', 'Ratking', 'LPL', 'LCK', 'LEC'],
                        'is_mature' => false
                    ],
                    [
                        'id' => '40627613557',
                        'user_id' => '92038375',
                        'user_login' => 'caedrel',
                        'user_name' => 'User Name 2',
                        'game_id' => '21779',
                        'game_name' => 'League of Legends',
                        'type' => 'live',
                        'title' => 'Stream Title 2',
                        'viewer_count' => 46181,
                        'started_at' => '2024-05-08T07:35:07Z',
                        'language' => 'en',
                        'thumbnail_url' => 'https://static-cdn.jtvnw.net/previews-ttv/live_user_caedrel-{width}x{height}.jpg',
                        'tag_ids' => [],
                        'tags' => ['xdd', 'Washed', 'degen', 'English', 'adhd', 'vtuber', 'Ratking', 'LPL', 'LCK', 'LEC'],
                        'is_mature' => false
                    ]
                ]
            ]),
            'http_code' => 500
        ];

        $streamsDataManager
            ->expects('getStreamsData')
            ->once()
            ->andReturn($getStreamsExpectedResponse);


        $getStreamsService = new GetStreamsService($streamsDataManager);
        $returnedStreams = $getStreamsService->execute();

        $this->assertEquals('503: {"error": "No se pueden devolver streams en este momento, inténtalo más tarde"}', $returnedStreams);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
