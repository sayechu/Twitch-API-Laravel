<?php

namespace Tests\Feature;

use Illuminate\Http\Response;
use App\Services\UserDataManager;
use Illuminate\Foundation\Testing\TestCase;
use App\Services\ApiClient;
use App\Services\DBClient;
use App\Services\TokenProvider;
use Mockery;

class GetStreamersTest extends TestCase
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;
    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    private const GET_USERS_ERROR_MESSAGE = 'No se pueden devolver usuarios en este momento, inténtalo más tarde';
    private const TWITCH_TOKEN = 'nrtovbe5h02os45krmjzvkt3hp74vf';
    private const GET_USERS_URL = 'https://api.twitch.tv/helix/users';
    private const ENDPOINT = "streamers";
    private const USER_ID = 1234;

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
            ->when(UserDataManager::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
    }

    /**
     * @test
     */
    public function get_users_with_token_stored_returns_user_data(): void
    {
        $usersResponse = [
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
            ->with(self::GET_USERS_URL . '?id=' . self::USER_ID, [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($usersResponse);

        $responseGetUsers = $this->get('/analytics/' . self::ENDPOINT . '?id=' . self::USER_ID);

        $responseGetUsers->assertContent('{"data":[{"id":"userId","login":"userLogin","display_name":"displayName","type":"type","broadcaster_type":"broadcasterType","description":"description","profile_image_url":"profileImageUrl","offline_image_url":"offlineImageUrl","view_count":0,"created_at":"createdAt"}]}');
    }

    /**
     * @test
     */
    public function get_users_with_token_from_api_returns_user_data()
    {
        $userResponse = [
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
            ->with(self::GET_USERS_URL . '?id=' . self::USER_ID, [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($userResponse);

        $responseGetUsers = $this->get('/analytics/' . self::ENDPOINT . '?id=' . self::USER_ID);

        $responseGetUsers->assertContent('{"data":[{"id":"userId","login":"userLogin","display_name":"displayName","type":"type","broadcaster_type":"broadcasterType","description":"description","profile_image_url":"profileImageUrl","offline_image_url":"offlineImageUrl","view_count":0,"created_at":"createdAt"}]}');
    }

    /**
     * @test
     */
    public function get_users_with_token_from_api_failure()
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

        $responseGetUsers = $this->get('/analytics/' . self::ENDPOINT . '?id=' . self::USER_ID);

        $responseGetUsers->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->assertEquals($expectedResponse, $responseGetUsers->getContent());
    }

    /**
     * @test
     */
    public function get_users_with_token_stored_returns_users_curl_error()
    {
        $expectedResponse = json_encode(['error' => self::GET_USERS_ERROR_MESSAGE]);
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
            ->with(self::GET_USERS_URL . '?id=' . self::USER_ID, [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->andReturn($curlCallResponse);

        $responseGetUsers = $this->get('/analytics/' . self::ENDPOINT . '?id=' . self::USER_ID);

        $responseGetUsers->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->assertEquals($expectedResponse, $responseGetUsers->getContent());
    }

    /**
     * @test
     */
    public function get_users_with_token_from_api_returns_users_curl_error()
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
        $expectedResponse = json_encode(['error' => self::GET_USERS_ERROR_MESSAGE]);

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
            ->with(self::GET_USERS_URL . '?id=' . self::USER_ID, [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->andReturn($curlCallResponse);

        $responseGetUsers = $this->get('/analytics/' . self::ENDPOINT . '?id=' . self::USER_ID);

        $responseGetUsers->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->assertEquals($expectedResponse, $responseGetUsers->getContent());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
