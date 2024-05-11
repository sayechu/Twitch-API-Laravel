<?php

namespace Tests\Feature;

use App\Services\UserDataManager;
use Illuminate\Foundation\Testing\TestCase;
use App\Services\ApiClient;
use App\Services\DBClient;
use App\Services\TokenProvider;
use Mockery;

class GetUsersTest extends TestCase
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;

    private const ERROR_GET_TOKEN_FAILED = 'No se puede establecer conexión con Twitch en este momento';
    private const ERROR_GET_USERS_FAILED = 'No se pueden devolver usuarios en este momento, inténtalo más tarde';
    private const TWITCH_TOKEN = 'nrtovbe5h02os45krmjzvkt3hp74vf';
    private const ERROR_STATUS = 503;

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
    public function test_get_users_with_token_stored_returns_user_data(): void
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
            'http_code' => 200
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
            ->with("https://api.twitch.tv/helix/users?id=1234", [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($usersResponse);

        $responseGetUsers = $this->get('/analytics/users?id=1234');

        $responseGetUsers->assertContent('{"data":[{"id":"userId","login":"userLogin","display_name":"displayName","type":"type","broadcaster_type":"broadcasterType","description":"description","profile_image_url":"profileImageUrl","offline_image_url":"offlineImageUrl","view_count":0,"created_at":"createdAt"}]}');
    }

    /**
     * @test
     */
    public function test_get_users_with_token_from_api_returns_user_data()
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
            'http_code' => 200
        ];
        $getTokenResponse = [
            'response' => json_encode([
                'access_token' => self::TWITCH_TOKEN,
                'expires_in' => 5089418,
                'token_type' => 'bearer'
            ]),
            'http_code' => 200
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
            ->with("https://api.twitch.tv/helix/users?id=1234", [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($userResponse);

        $responseGetUsers = $this->get('/analytics/users?id=1234');

        $responseGetUsers->assertContent('{"data":[{"id":"userId","login":"userLogin","display_name":"displayName","type":"type","broadcaster_type":"broadcasterType","description":"description","profile_image_url":"profileImageUrl","offline_image_url":"offlineImageUrl","view_count":0,"created_at":"createdAt"}]}');
    }

    /**
     * @test
     */
    public function test_get_users_with_token_request_to_api_failure()
    {
        $expectedResponse = json_encode(['error' => self::ERROR_GET_TOKEN_FAILED]);
        $getTokenResponse = [
            'response' => null,
            'http_code' => 500
        ];

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(false);
        $this->apiClient
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenResponse);

        $responseGetUsers = $this->get('/analytics/users?id=1234');

        $responseGetUsers->assertStatus(self::ERROR_STATUS);
        $this->assertEquals($expectedResponse, $responseGetUsers->getContent());
    }

    /**
     * @test
     */
    public function test_get_users_with_token_stored_and_error_in_user_curl_call()
    {
        $expectedResponse = json_encode(['error' => self::ERROR_GET_USERS_FAILED]);
        $curlCallResponse = [
            'response' => null,
            'http_code' => 500
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
            ->with('https://api.twitch.tv/helix/users?id=1234', [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->andReturn($curlCallResponse);

        $responseGetUsers = $this->get('/analytics/users?id=1234');

        $responseGetUsers->assertStatus(self::ERROR_STATUS);
        $this->assertEquals($expectedResponse, $responseGetUsers->getContent());
    }

    /**
     * @test
     */
    public function test_get_users_with_token_request_to_api_and_error_in_user_curl_call()
    {
        $getTokenResponse = [
            'response' => json_encode([
                'access_token' => self::TWITCH_TOKEN,
                'expires_in' => 5089418,
                'token_type' => 'bearer'
            ]),
            'http_code' => 200
        ];
        $curlCallResponse = [
            'response' => null,
            'http_code' => 500
        ];
        $expectedResponse = json_encode(['error' => self::ERROR_GET_USERS_FAILED]);

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
            ->with('https://api.twitch.tv/helix/users?id=1234', [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->andReturn($curlCallResponse);

        $responseGetUsers = $this->get('/analytics/users?id=1234');

        $responseGetUsers->assertStatus(self::ERROR_STATUS);
        $this->assertEquals($expectedResponse, $responseGetUsers->getContent());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
