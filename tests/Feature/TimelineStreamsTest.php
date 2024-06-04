<?php

namespace Tests\Feature;

use App\Services\ApiClient;
use App\Services\TimelineStreamersProvider;
use App\Services\TimelineStreamsProvider;
use App\Services\TokenProvider;
use Tests\TestCase;
use App\Services\DBClient;
use Illuminate\Http\Response;
use Mockery;

class TimelineStreamsTest extends TestCase
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;
    private const USERNAME = "username";
    private const TWITCH_TOKEN = 'nrtovbe5h02os45krmjzvkt3hp74vf';
    private const ENDPOINT = "/analytics/timeline";
    private const NOT_FOUND_ERROR_MESSAGE = "El usuario especificado ( " . self::USERNAME . " ) no existe.";
    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    private const INTERNAL_SERVER_ERROR_MESSAGE = 'Error del servidor al obtener el timeline.';

    protected function setUp(): void
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
            ->when(TimelineStreamersProvider::class)
            ->needs(DBClient::class)
            ->give(fn() => $this->databaseClient);
        $this->app
            ->when(TimelineStreamsProvider::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
        $this->app
            ->when(TimelineStreamsProvider::class)
            ->needs(DBClient::class)
            ->give(fn() => $this->databaseClient);
    }

    /**
     * @test
     */
    public function get_streamers_timeline_returns_not_found_error()
    {
        $this->databaseClient
            ->expects('isUserStored')
            ->once()
            ->with(self::USERNAME)
            ->andReturn(false);
        $responseGetTimeline = $this->get(self::ENDPOINT . '?username=' . self::USERNAME);

        $responseGetTimeline->assertStatus(Response::HTTP_NOT_FOUND);
        $responseGetTimeline->assertJson([
            'error' => self::NOT_FOUND_ERROR_MESSAGE
        ]);
    }

    /**
     * @test
     */
    public function get_streamers_timeline_returns_token_error()
    {
        $followingStreamers = ['streamer1', 'streamer2'];
        $getTokenResponse = [
            'response' => null,
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

        $this->databaseClient
            ->expects('isUserStored')
            ->once()
            ->with(self::USERNAME)
            ->andReturn(true);
        $this->databaseClient
            ->expects('getStreamers')
            ->once()
            ->with(self::USERNAME)
            ->andReturn($followingStreamers);
        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(false);
        $this->apiClient
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenResponse);

        $responseGetTimeline = $this->get(self::ENDPOINT . '?username=' . self::USERNAME);

        $responseGetTimeline->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $responseGetTimeline->assertJson([
            'error' => self::GET_TOKEN_ERROR_MESSAGE
        ]);
    }

    /**
     * @test
     */
    public function get_streamers_timeline_with_stored_token_returns_stream_curl_error()
    {
        $followingStreamers = ['streamer1', 'streamer2'];
        $streamsResponse = [
            'response' => json_encode([
                'data' => [
                    [
                        'id' => 'userId',
                        'login' => 'userLogin',
                        'display_name' => 'displayName',
                        'type' => 'type',
                        'broadcaster_type' => 'broadcasterType',
                        'description' => 'description',
                        'profile_image_url' => 'profileImageUrl',
                        'offline_image_url' => 'offlineImageUrl',
                        'view_count' => 0,
                        'created_at' => 'createdAt'
                    ]
                ]
            ]),
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

        $this->databaseClient
            ->expects('isUserStored')
            ->once()
            ->with(self::USERNAME)
            ->andReturn(true);
        $this->databaseClient
            ->expects('getStreamers')
            ->once()
            ->with(self::USERNAME)
            ->andReturn($followingStreamers);
        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(true);
        $this->databaseClient
            ->expects('getToken')
            ->once()
            ->andReturn(self::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->once()
            ->andReturn($streamsResponse);

        $responseGetTimeline = $this->get(self::ENDPOINT . '?username=' . self::USERNAME);

        $responseGetTimeline->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $responseGetTimeline->assertJson([
            'error' => self::INTERNAL_SERVER_ERROR_MESSAGE
        ]);
    }

    /**
     * @test
     */
    public function get_streamers_timeline()
    {
        $followingStreamers = ['streamer1'];
        $streamsResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => 'userId',
                        'login' => 'userLogin',
                        'display_name' => 'displayName',
                        'type' => 'type',
                        'broadcaster_type' => 'broadcasterType',
                        'description' => 'description',
                        'profile_image_url' => 'profileImageUrl',
                        'offline_image_url' => 'offlineImageUrl',
                        'view_count' => 0,
                        'created_at' => 'createdAt'
                    ]
                ]
            ],
            'http_code' => Response::HTTP_OK
        ];
        $expectedResponse = [
            "streamerId" => "streamer1",
            "streamerName" => "Streamer 1",
            "title" => "Stream 1",
            "game" => "Game 1",
            "viewerCount" => 100,
            "startedAt" => "2024-05-10T12:00:00Z"
        ];

        $this->databaseClient
            ->expects('isUserStored')
            ->once()
            ->with(self::USERNAME)
            ->andReturn(true);
        $this->databaseClient
            ->expects('getStreamers')
            ->once()
            ->with(self::USERNAME)
            ->andReturn($followingStreamers);
        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(true);
        $this->databaseClient
            ->expects('getToken')
            ->once()
            ->andReturn(self::TWITCH_TOKEN);
        $this->apiClient
            ->shouldReceive('makeCurlCall')
            ->andReturn($streamsResponse);
        $this->databaseClient
            ->shouldReceive('storeStreams');
        $this->databaseClient
            ->expects('getTimelineStreams')
            ->once()
            ->andReturn([$expectedResponse]);

        $responseGetTimeline = $this->get(self::ENDPOINT . '?username=' . self::USERNAME);

        $responseGetTimeline->assertStatus(Response::HTTP_OK);
        $responseGetTimeline->assertJson([
            $expectedResponse
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
