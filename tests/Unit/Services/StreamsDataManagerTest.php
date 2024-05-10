<?php

namespace Tests\Unit\Services;

use App\Services\ApiClient;
use App\Services\StreamsDataManager;
use App\Services\TokenProvider;
use Mockery;
use Tests\TestCase;

class StreamsDataManagerTest extends TestCase
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;
    private StreamsDataManager $streamsDataManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenProvider = Mockery::mock(TokenProvider::class);
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->streamsDataManager = new StreamsDataManager($this->tokenProvider, $this->apiClient);
    }

    /**
     * @test
     */
    public function test_get_streams_data(): void
    {
        $tokenResponse = 'nrtovbe5h02os45krmjzvkt3hp74vf';
        $curlCallResponse = [
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
        $expectedResponse = [
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

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($tokenResponse);
        $this->apiClient
            ->expects('makeCurlCall')
            ->once()
            ->andReturn($curlCallResponse);

        $returnedStreams = $this->streamsDataManager->getStreamsData();

        $this->assertEquals($expectedResponse, $returnedStreams);
    }

    /**
     * @test
     */
    public function test_get_streams_data_with_token_error(): void
    {
        $expectedResponse = '503: {"error": "No se puede establecer conexión con Twitch en este momento}';
        $tokenResponse = [
            "response" => null,
            "http_code" => 500
        ];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($tokenResponse);

        $returnedStreams = $this->streamsDataManager->getStreamsData();

        $this->assertEquals($expectedResponse, $returnedStreams);
    }

    /**
     * @test
     */
    public function test_get_streams_data_with_correct_token_but_curl_error(): void
    {
        $tokenResponse = 'nrtovbe5h02os45krmjzvkt3hp74vf';
        $streamsResponse = [
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
        $expectedResponse = '503: {"error": "No se pueden devolver streams en este momento, inténtalo más tarde"}';

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($tokenResponse);
        $this->apiClient
            ->expects('makeCurlCall')
            ->once()
            ->andReturn($streamsResponse);

        $returnedStreams = $this->streamsDataManager->getStreamsData();

        $this->assertEquals($expectedResponse, $returnedStreams);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
