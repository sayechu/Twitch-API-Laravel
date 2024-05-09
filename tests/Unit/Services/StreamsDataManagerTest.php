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
    public function get_streams_data_token_failure(): void
    {
        $tokenExpectedResponse = array(
            "response" => null,
            "http_code" => 500
        );

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($tokenExpectedResponse);

        $returnedStreams = $this->streamsDataManager->getStreamsData();

        $this->assertEquals('503: {"error": "No se puede establecer conexión con Twitch en este momento}', $returnedStreams);
    }

    /**
     * @test
     */
    public function get_streams_data_token_correct_curl_failure(): void
    {
        $tokenExpectedResponse = 'nrtovbe5h02os45krmjzvkt3hp74vf';

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($tokenExpectedResponse);

        $streamsExpectedResponse = [
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

        $this->apiClient
            ->expects('makeCurlCall')
            ->once()
            ->andReturn($streamsExpectedResponse);

        $returnedStreams = $this->streamsDataManager->getStreamsData();

        $this->assertEquals(500, $returnedStreams['http_code']);
    }

    /**
     * @test
     */
    public function get_streams_data_test(): void
    {
        $tokenExpectedResponse = 'nrtovbe5h02os45krmjzvkt3hp74vf';

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($tokenExpectedResponse);

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

        $this->apiClient
            ->expects('makeCurlCall')
            ->once()
            ->andReturn($getStreamsExpectedResponse);

        $returnedStreams = $this->streamsDataManager->getStreamsData();

        $this->assertEquals(200, $returnedStreams['http_code']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
