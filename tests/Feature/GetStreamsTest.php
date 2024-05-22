<?php

namespace Tests\Feature;

use Illuminate\Http\Response;
use App\Services\ApiClient;
use App\Services\DBClient;
use App\Services\StreamsDataManager;
use App\Services\TokenProvider;
use Tests\StreamDTO;
use Tests\StreamDTOBuilder;
use Tests\TestCase;
use Mockery;

class GetStreamsTest extends TestCase
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;
    private StreamDTO $expectedStream;
    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    private const GET_STREAMS_ERROR_MESSAGE = 'No se pueden devolver streams en este momento, inténtalo más tarde';
    private const TOKEN = "nrtovbe5h02os45krmjzvkt3hp74vf";

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
        $this->expectedStream = (new StreamDTOBuilder())
            ->withId('40627613557')
            ->withUserId('92038375')
            ->withUserLogin('caedrel')
            ->withUserName('User Name')
            ->withGameId('21779')
            ->withGameName('League of Legends')
            ->withType('live')
            ->withTitle('Stream Title')
            ->withViewerCount(46181)
            ->withStartedAt('2024-05-08T07:35:07Z')
            ->withLanguage('en')
            ->withThumbnailUrl('https://static-cdn.jtvnw.net/previews-ttv/live_user_caedrel-{width}x{height}.jpg')
            ->withTags(['xdd', 'Washed', 'degen', 'English', 'adhd', 'vtuber', 'Ratking', 'LPL', 'LCK', 'LEC'])
            ->withIsMature(false)
            ->build();
    }

    /**
     * @test
     */
    public function gets_streams_with_stored_token(): void
    {
        $getStreamsResponse = [
            'response' => json_encode(['data' => [$this->expectedStream->toArray()]]),
            'http_code' => Response::HTTP_OK
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
            'response' => json_encode(['data' => [$this->expectedStream->toArray()]]),
            'http_code' => Response::HTTP_OK
        ];

        $getTokenExpectedResponse = [
            "response" => '{"access_token":"' . self::TOKEN . '","expires_in":5590782,"token_type":"bearer"}',
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
            ->with(self::TOKEN)
            ->once();
        $this->apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/streams", [0 => 'Authorization: Bearer ' . self::TOKEN])
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
            "response" => '{"access_token":"' . self::TOKEN . '","expires_in":5590782,"token_type":"bearer"}',
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
            ->with(self::TOKEN)
            ->once();
        $this->apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/streams", [0 => 'Authorization: Bearer ' . self::TOKEN])
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
