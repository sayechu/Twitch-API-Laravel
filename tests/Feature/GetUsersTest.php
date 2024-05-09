<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase;
use App\Services\ApiClient;
use App\Services\DBClient;
use App\Services\TokenProvider;
use App\Services\UserDataProvider;
use Mockery;

class GetUsersTest extends TestCase
{
    /**
     * @test
     */
    public function get_users(): void
    {
        $apiClient = Mockery::mock(ApiClient::class);
        $databaseClient = Mockery::mock(DBClient::class);
        $this->app
            ->when(TokenProvider::class)
            ->needs(ApiClient::class)
            ->give(fn() => $apiClient);
        $this->app
            ->when(TokenProvider::class)
            ->needs(DBClient::class)
            ->give(fn() => $databaseClient);
        $this->app
            ->when(UserDataProvider::class)
            ->needs(ApiClient::class)
            ->give(fn() => $apiClient);

        $expectedUsersResponse = [
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
        $databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(true);
        $databaseClient
            ->expects('getToken')
            ->once()
            ->andReturn('nrtovbe5h02os45krmjzvkt3hp74vf');
        $apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/users?id=1234", [0 => 'Authorization: Bearer nrtovbe5h02os45krmjzvkt3hp74vf'])
            ->once()
            ->andReturn($expectedUsersResponse);

        $responseGetUsers = $this->get('/analytics/users?id=1234');

        $responseGetUsers->assertContent('{"data":[{"id":"userId","login":"userLogin","display_name":"displayName","type":"type","broadcaster_type":"broadcasterType","description":"description","profile_image_url":"profileImageUrl","offline_image_url":"offlineImageUrl","view_count":0,"created_at":"createdAt"}]}');
    }
}
