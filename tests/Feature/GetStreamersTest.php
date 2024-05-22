<?php

namespace Tests\Feature;

use Illuminate\Http\Response;
use App\Services\StreamersDataManager;
use Illuminate\Foundation\Testing\TestCase;
use App\Services\ApiClient;
use App\Services\DBClient;
use App\Services\TokenProvider;
use Mockery;
use Tests\Builders\StreamerDTO;
use Tests\Builders\StreamerDTOBuilder;

class GetStreamersTest extends TestCase
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;
    private StreamerDTO $expectedStreamerDTO;
    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    private const GET_STREAMERS_ERROR_MESSAGE = 'No se pueden devolver usuarios en este momento, inténtalo más tarde';
    private const TWITCH_TOKEN = 'nrtovbe5h02os45krmjzvkt3hp74vf';
    private const GET_STREAMERS_URL = 'https://api.twitch.tv/helix/users';
    private const ENDPOINT = "streamers";
    private const STREAMER_ID = 1234;

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
            ->when(StreamersDataManager::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
        $this->expectedStreamerDTO = (new StreamerDTOBuilder())
            ->withId('userId')
            ->withLogin('userLogin')
            ->withDisplayName('displayName')
            ->withType('type')
            ->withBroadcasterType('broadcasterType')
            ->withDescription('description')
            ->withProfileImageUrl('profileImageUrl')
            ->withOfflineImageUrl('offlineImageUrl')
            ->withViewCount(0)
            ->withCreatedAt('2024-05-08T07:35:07Z')
            ->build();
    }

    /**
     * @test
     */
    public function get_streamers_with_token_stored_returns_user_data(): void
    {
        $streamersResponse = [
            'response' => json_encode([
                'data' => [$this->expectedStreamerDTO->toArray()]
            ]),
            'http_code' => Response::HTTP_OK
        ];

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
            ->with(self::GET_STREAMERS_URL . '?id=' . self::STREAMER_ID, [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($streamersResponse);

        $responseGetStreamers = $this->get('/analytics/' . self::ENDPOINT . '?id=' . self::STREAMER_ID);

        $responseGetStreamers->assertContent('{"data":[{"id":"userId","login":"userLogin","display_name":"displayName","type":"type","broadcaster_type":"broadcasterType","description":"description","profile_image_url":"profileImageUrl","offline_image_url":"offlineImageUrl","view_count":0,"created_at":"2024-05-08T07:35:07Z"}]}');
    }

    /**
     * @test
     */
    public function get_streamers_with_token_from_api_returns_user_data()
    {
        $streamersResponse = [
            'response' => json_encode([
                'data' => [$this->expectedStreamerDTO->toArray()]
            ]),
            'http_code' => Response::HTTP_OK
        ];
        $getTokenResponse = [
            'response' => json_encode([
                'access_token' => self::TWITCH_TOKEN,
                'expires_in' => 5089418,
                'token_type' => 'bearer'
            ]),
            'http_code' => Response::HTTP_OK
        ];

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
            ->once()
            ->with(self::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(self::GET_STREAMERS_URL . '?id=' . self::STREAMER_ID, [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($streamersResponse);

        $responseGetStreamers = $this->get('/analytics/' . self::ENDPOINT . '?id=' . self::STREAMER_ID);

        $responseGetStreamers->assertContent('{"data":[{"id":"userId","login":"userLogin","display_name":"displayName","type":"type","broadcaster_type":"broadcasterType","description":"description","profile_image_url":"profileImageUrl","offline_image_url":"offlineImageUrl","view_count":0,"created_at":"2024-05-08T07:35:07Z"}]}');
    }

    /**
     * @test
     */
    public function get_streamers_with_token_from_api_failure()
    {
        $expectedResponse = json_encode(['error' => self::GET_TOKEN_ERROR_MESSAGE]);
        $getTokenResponse = [
            'response' => null,
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(false);
        $this->apiClient
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenResponse);

        $responseGetStreamers = $this->get('/analytics/' . self::ENDPOINT . '?id=' . self::STREAMER_ID);

        $responseGetStreamers->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->assertEquals($expectedResponse, $responseGetStreamers->getContent());
    }

    /**
     * @test
     */
    public function get_streamers_with_token_stored_returns_users_curl_error()
    {
        $expectedResponse = json_encode(['error' => self::GET_STREAMERS_ERROR_MESSAGE]);
        $curlCallResponse = [
            'response' => null,
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

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
            ->with(self::GET_STREAMERS_URL . '?id=' . self::STREAMER_ID, [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->andReturn($curlCallResponse);

        $responseGetStreamers = $this->get('/analytics/' . self::ENDPOINT . '?id=' . self::STREAMER_ID);

        $responseGetStreamers->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->assertEquals($expectedResponse, $responseGetStreamers->getContent());
    }

    /**
     * @test
     */
    public function get_streamers_with_token_from_api_returns_users_curl_error()
    {
        $getTokenResponse = [
            'response' => json_encode([
                'access_token' => self::TWITCH_TOKEN,
                'expires_in' => 5089418,
                'token_type' => 'bearer'
            ]),
            'http_code' => Response::HTTP_OK
        ];
        $curlCallResponse = [
            'response' => null,
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];
        $expectedResponse = json_encode(['error' => self::GET_STREAMERS_ERROR_MESSAGE]);

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
            ->once()
            ->with(self::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->once()
            ->with(self::GET_STREAMERS_URL . '?id=' . self::STREAMER_ID, [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->andReturn($curlCallResponse);

        $responseGetStreamers = $this->get('/analytics/' . self::ENDPOINT . '?id=' . self::STREAMER_ID);

        $responseGetStreamers->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->assertEquals($expectedResponse, $responseGetStreamers->getContent());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
