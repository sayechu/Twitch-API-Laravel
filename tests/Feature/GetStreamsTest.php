<?php

namespace Tests\Feature;

use Illuminate\Http\Response;
use App\Services\ApiClient;
use App\Services\DBClient;
use App\Services\StreamsDataManager;
use App\Services\TokenProvider;
use Tests\Builders\AnalyticsParameters;
use Tests\TestCase;
use Mockery;

class GetStreamsTest extends TestCase
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;
    public const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    public const GET_STREAMS_ERROR_MESSAGE = 'No se pueden devolver streams en este momento, inténtalo más tarde';

    protected function setUp() : void
    {
        parent::setUp();
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->databaseClient = Mockery::mock(DBClient::class);
        $this->app
            ->when(TokenProvider::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
        $this->app
            ->when(TokenProvider::class)
            ->needs(DBClient::class)
            ->give(fn() => $this->databaseClient);
        $this->app
            ->when(StreamsDataManager::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
    }

    /**
     * @test
     */
    public function gets_streams_with_stored_token(): void
    {
        $getStreamsResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => '40627613557',
                        'user_id' => '92038375',
                        'user_login' => 'caedrel',
                        'user_name' => 'User Name',
                        'game_id' => '21779',
                        'game_name' => 'League of Legends',
                        'type' => 'live',
                        'title' => 'Stream Title',
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

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(true);
        $this->databaseClient
            ->expects('getToken')
            ->once()
            ->andReturn(AnalyticsParameters::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/streams", [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->once()
            ->andReturn($getStreamsResponse);

        $responseGetStreams = $this->get('/analytics/streams');

        $responseGetStreams->assertStatus(Response::HTTP_OK);
        $responseGetStreams->assertContent('[{"title":"Stream Title","user_name":"User Name"}]');
    }

    /**
     * @test
     */
    public function gets_streams_without_stored_token(): void
    {
        $getStreamsResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => '40627613557',
                        'user_id' => '92038375',
                        'user_login' => 'caedrel',
                        'user_name' => 'User Name',
                        'game_id' => '21779',
                        'game_name' => 'League of Legends',
                        'type' => 'live',
                        'title' => 'Stream Title',
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
        $getTokenExpectedResponse = [
            "response" => '{"access_token":"' . AnalyticsParameters::TWITCH_TOKEN . '","expires_in":5590782,"token_type":"bearer"}',
            "http_code" => Response::HTTP_OK
        ];

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(false);
        $this->apiClient
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenExpectedResponse);
        $this->databaseClient
            ->expects('storeToken')
            ->with(AnalyticsParameters::TWITCH_TOKEN)
            ->once();
        $this->apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/streams", [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->once()
            ->andReturn($getStreamsResponse);

        $responseGetStreams = $this->get('/analytics/streams');

        $responseGetStreams->assertContent('[{"title":"Stream Title","user_name":"User Name"}]');
    }

    /**
     * @test
     */
    public function gets_streams_without_stored_token_returns_token_curl_error(): void
    {
        $getTokenResponse = [
            "response" => null,
            "http_code" => Response::HTTP_INTERNAL_SERVER_ERROR
        ];
        $expectedResponse = json_encode(['error' => self::GET_TOKEN_ERROR_MESSAGE]);

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(false);
        $this->apiClient
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenResponse);

        $responseGetStreams = $this->get('/analytics/streams');

        $responseGetStreams->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
        $responseGetStreams->assertContent($expectedResponse);
    }

    /**
     * @test
     */
    public function gets_streams_without_token_stored_returns_streams_curl_error(): void
    {
        $getStreamsResponse = [
            'response' => null,
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];
        $getTokenResponse = [
            "response" => '{"access_token":"' . AnalyticsParameters::TWITCH_TOKEN . '","expires_in":5590782,"token_type":"bearer"}',
            "http_code" => Response::HTTP_OK
        ];
        $expectedResponse = json_encode(['error' => self::GET_STREAMS_ERROR_MESSAGE]);

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(false);
        $this->apiClient
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenResponse);
        $this->databaseClient
            ->expects('storeToken')
            ->with(AnalyticsParameters::TWITCH_TOKEN)
            ->once();
        $this->apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/streams", [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->once()
            ->andReturn($getStreamsResponse);

        $responseGetStreams = $this->get('/analytics/streams');

        $responseGetStreams->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
        $responseGetStreams->assertContent($expectedResponse);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
