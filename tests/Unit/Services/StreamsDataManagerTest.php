<?php

namespace Tests\Unit\Services;

use Illuminate\Http\Response;
use App\Services\ApiClient;
use App\Services\StreamsDataManager;
use App\Services\TokenProvider;
use Mockery;
use Tests\Builders\AnalyticsParameters;
use Tests\Feature\GetStreamersTest;
use Tests\TestCase;

class StreamsDataManagerTest extends TestCase
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;
    private StreamsDataManager $streamsDataManager;
    private const GET_STREAMS_ERROR_MESSAGE = 'No se pueden devolver streams en este momento, inténtalo más tarde';

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
    public function get_streams_data_returns_streams_data(): void
    {
        $tokenResponse = AnalyticsParameters::TWITCH_TOKEN;
        $curlCallResponse = [
            'response' => [
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
            ],
            'http_code' => Response::HTTP_OK
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
            ->with(AnalyticsParameters::ANALYTICS_GET_STREAMS_URL, [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->once()
            ->andReturn($curlCallResponse);

        $returnedStreams = $this->streamsDataManager->getStreamsData();

        $this->assertEquals($expectedResponse, $returnedStreams);
    }

    /**
     * @test
     */
    public function get_streams_data_returns_token_error(): void
    {
        $expectedExceptionMessage = GetStreamersTest::GET_TOKEN_ERROR_MESSAGE;
        $tokenResponse = [
            "response" => null,
            "http_code" => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($tokenResponse);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->streamsDataManager->getStreamsData();
    }

    /**
     * @test
     */
    public function get_streams_data_returns_streams_curl_error(): void
    {
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
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];
        $expectedExceptionMessage = self::GET_STREAMS_ERROR_MESSAGE;


        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn(AnalyticsParameters::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(AnalyticsParameters::ANALYTICS_GET_STREAMS_URL, [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->once()
            ->andReturn($streamsResponse);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->streamsDataManager->getStreamsData();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
