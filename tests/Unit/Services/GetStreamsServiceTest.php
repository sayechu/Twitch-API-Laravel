<?php

namespace Tests\Unit\Services;

use App\Services\GetStreamsService;
use App\Services\StreamsDataManager;
use Mockery;
use Tests\TestCase;

class GetStreamsServiceTest extends TestCase
{
    private GetStreamsService $getStreamsService;
    private StreamsDataManager $streamsDataManager;
    const ERROR_GET_TOKEN_FAILED = 'No se puede establecer conexión con Twitch en este momento';
    const ERROR_GET_STREAMS_FAILED = 'No se pueden devolver streams en este momento, inténtalo más tarde';

    protected function setUp(): void
    {
        parent::setUp();
        $this->streamsDataManager = Mockery::mock(StreamsDataManager::class);
        $this->getStreamsService = new GetStreamsService($this->streamsDataManager);
    }

    /**
     * @test
     */
    public function test_execute(): void
    {
        $streamsResponse = [
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

        $this->streamsDataManager
            ->expects('getStreamsData')
            ->once()
            ->andReturn($streamsResponse);

        $returnedStreams = $this->getStreamsService->execute();

        $this->assertEquals($expectedFilteredStreams, $returnedStreams);
    }

    /**
     * @test
     */
    public function test_execute_with_token_error(): void
    {
        $streamsResponse = ['error' => self::ERROR_GET_TOKEN_FAILED];

        $this->streamsDataManager
            ->expects('getStreamsData')
            ->once()
            ->andReturn($streamsResponse);

        $returnedStreams = $this->getStreamsService->execute();

        $this->assertEquals($streamsResponse, $returnedStreams);
    }

    /**
     * @test
     */
    public function execute_token_correct_curl_error(): void
    {
        $streamsResponse = ['error' => self::ERROR_GET_STREAMS_FAILED];

        $this->streamsDataManager
            ->expects('getStreamsData')
            ->once()
            ->andReturn($streamsResponse);

        $returnedStreams = $this->getStreamsService->execute();

        $this->assertEquals($streamsResponse, $returnedStreams);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
