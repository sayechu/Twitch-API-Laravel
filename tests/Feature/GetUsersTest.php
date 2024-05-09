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
            ->andReturn('nrtovbe5h02os45krmjzvkt3hp74vf');
        $this->apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/users?id=1234", [0 => 'Authorization: Bearer nrtovbe5h02os45krmjzvkt3hp74vf'])
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
                'access_token' => 'rz2po6wrbgami5k2qjk0e4q0vwschm',
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
            ->with('rz2po6wrbgami5k2qjk0e4q0vwschm');
        $this->apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/users?id=1234", [0 => 'Authorization: Bearer rz2po6wrbgami5k2qjk0e4q0vwschm'])
            ->once()
            ->andReturn($userResponse);

        $responseGetUsers = $this->get('/analytics/users?id=1234');

        $responseGetUsers->assertContent('{"data":[{"id":"userId","login":"userLogin","display_name":"displayName","type":"type","broadcaster_type":"broadcasterType","description":"description","profile_image_url":"profileImageUrl","offline_image_url":"offlineImageUrl","view_count":0,"created_at":"createdAt"}]}');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
