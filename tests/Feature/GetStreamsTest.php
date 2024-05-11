<?php

namespace Tests\Feature;

use App\Services\ApiClient;
use App\Services\DBClient;
use App\Services\StreamsDataManager;
use App\Services\TokenProvider;
use Tests\TestCase;
use Mockery;

class GetStreamsTest extends TestCase
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;
    const ERROR_GET_TOKEN_FAILED = 'No se puede establecer conexión con Twitch en este momento';
    const ERROR_GET_STREAMS_FAILED = 'No se pueden devolver streams en este momento, inténtalo más tarde';
    const ERROR_STATUS = 503;
    const TOKEN = "nrtovbe5h02os45krmjzvkt3hp74vf";

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
    public function test_gets_streams_with_token_stored_returns_streams(): void
    {
        $getStreamsExpectedResponse = [
            'response' => json_encode([
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
            ]),
            'http_code' => 200
        ];

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(true);
        $this->databaseClient
            ->expects('getToken')
            ->once()
            ->andReturn(self::TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/streams", [0 => 'Authorization: Bearer ' . self::TOKEN])
            ->once()
            ->andReturn($getStreamsExpectedResponse);

        $responseGetStreams = $this->get('/analytics/streams');

        $responseGetStreams->assertStatus(200);
        $responseGetStreams->assertContent('[{"title":"Stream Title","user_name":"User Name"}]');
    }

    /**
     * @test
     */
    public function test_gets_streams_without_token_stored_returns_streams(): void
    {
        $getStreamsExpectedResponse = [
            'response' => json_encode([
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
            ]),
            'http_code' => 200
        ];
        $getTokenExpectedResponse = [
            "response" => '{"access_token":"' . self::TOKEN . '","expires_in":5590782,"token_type":"bearer"}',
            "http_code" => 200
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
            ->with(self::TOKEN)
            ->once();
        $this->apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/streams", [0 => 'Authorization: Bearer ' . self::TOKEN])
            ->once()
            ->andReturn($getStreamsExpectedResponse);

        $responseGetStreams = $this->get('/analytics/streams');

        $responseGetStreams->assertContent('[{"title":"Stream Title","user_name":"User Name"}]');
    }

    /**
     * @test
     */
    public function test_gets_streams_without_token_stored_returns_token_curl_error(): void
    {
        $getTokenExpectedResponse = [
            "response" => null,
            "http_code" => 500
        ];
        $expectedResponse = json_encode(['error' => self::ERROR_GET_TOKEN_FAILED]);

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(false);
        $this->apiClient
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenExpectedResponse);

        $responseGetStreams = $this->get('/analytics/streams');

        $responseGetStreams->assertStatus(self::ERROR_STATUS);
        $responseGetStreams->assertContent($expectedResponse);
    }

    /**
     * @test
     */
    public function test_gets_streams_without_token_stored_returns_streams_curl_error(): void
    {
        $getStreamsExpectedResponse = [
            'response' => null,
            'http_code' => 500
        ];
        $getTokenExpectedResponse = [
            "response" => '{"access_token":"' . self::TOKEN . '","expires_in":5590782,"token_type":"bearer"}',
            "http_code" => 200
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
            ->with(self::TOKEN)
            ->once();
        $this->apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/streams", [0 => 'Authorization: Bearer ' . self::TOKEN])
            ->once()
            ->andReturn($getStreamsExpectedResponse);
        $expectedResponse = json_encode(['error' => self::ERROR_GET_STREAMS_FAILED]);

        $responseGetStreams = $this->get('/analytics/streams');

        $responseGetStreams->assertStatus(self::ERROR_STATUS);
        $responseGetStreams->assertContent($expectedResponse);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
