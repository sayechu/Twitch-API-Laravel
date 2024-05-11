<?php

namespace Tests\Unit\Services;

use App\Services\ApiClient;
use App\Services\TokenProvider;
use App\Services\UserDataManager;
use Mockery;
use Tests\TestCase;

class UserDataManagerTest extends TestCase
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;
    private UserDataManager $userDataManager;

    private const ERROR_GET_TOKEN_FAILED = 'No se puede establecer conexión con Twitch en este momento';
    private const ERROR_GET_USERS_FAILED = 'No se pueden devolver usuarios en este momento, inténtalo más tarde';

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenProvider = Mockery::mock(TokenProvider::class);
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->userDataManager = new UserDataManager($this->tokenProvider, $this->apiClient);

        $this->app
            ->when(UserDataManager::class)
            ->needs(TokenProvider::class)
            ->give(fn() => $this->tokenProvider);
        $this->app
            ->when(UserDataManager::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
    }

    /**
     * @test
     */
    public function get_user_data(): void
    {
        $getTokenResponse = 'nrtovbe5h02os45krmjzvkt3hp74vf';
        $getUserDataResponse = [
            'response' => json_encode([
                'data' => [
                    [
                        'id' => '1234',
                        'login' => 'zdraste_vladkenov',
                        'display_name' => 'zdraste_vladkenov',
                        'type' => '',
                        'broadcaster_type' => '',
                        'description' => 'wasde876',
                        'profile_image_url' => 'https://static-cdn.jtvnw.net/user-default-pictures-uv/ebe4cd89-b4f4-4cd9-adac-2f30151b4209-profile_image-300x300.png',
                        'offline_image_url' => '',
                        'view_count' => 0,
                        'created_at' => '2018-09-04T15:23:04Z'
                    ]
                ]
            ]),
            'http_code' => 200
        ];
        $expectedGetUserDataResponse = [
            "data" => [
                [
                    "id" => "1234",
                    "login" => "zdraste_vladkenov",
                    "display_name" => "zdraste_vladkenov",
                    "type" => "",
                    "broadcaster_type" => "",
                    "description" => "wasde876",
                    "profile_image_url" => "https://static-cdn.jtvnw.net/user-default-pictures-uv/ebe4cd89-b4f4-4cd9-adac-2f30151b4209-profile_image-300x300.png",
                    "offline_image_url" => "",
                    "view_count" => 0,
                    "created_at" => "2018-09-04T15:23:04Z"
                ]
            ]
        ];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenResponse);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with('https://api.twitch.tv/helix/users?id=1234', [0 => 'Authorization: Bearer nrtovbe5h02os45krmjzvkt3hp74vf'])
            ->once()
            ->andReturn($getUserDataResponse);

        $returnedUserInfo = $this->userDataManager->getUserData('1234');

        $this->assertEquals($expectedGetUserDataResponse, $returnedUserInfo);
    }

    /**
     * @test
     */
    public function get_user_data_token_failure(): void
    {
        $getTokenResponse = [
            'response' => json_encode([
                'access_token' => 'rz2po6wrbgami5k2qjk0e4q0vwschm',
                'expires_in' => 5089418,
                'token_type' => 'bearer'
            ]),
            'http_code' => 500
        ];
        $expectedResponse = ['error' => self::ERROR_GET_TOKEN_FAILED];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenResponse);

        $returnedUserInfo = $this->userDataManager->getUserData('1234');

        $this->assertEquals($expectedResponse, $returnedUserInfo);
    }

    /**
     * @test
     */
    public function get_user_data_token_passes_curl_call_fails(): void
    {
        $getTokenResponse = 'nrtovbe5h02os45krmjzvkt3hp74vf';
        $getUserDataResponse = [
            'response' => json_encode([
                'data' => [
                    [
                        'id' => '1234',
                        'login' => 'zdraste_vladkenov',
                        'display_name' => 'zdraste_vladkenov',
                        'type' => '',
                        'broadcaster_type' => '',
                        'description' => 'wasde876',
                        'profile_image_url' => 'https://static-cdn.jtvnw.net/user-default-pictures-uv/ebe4cd89-b4f4-4cd9-adac-2f30151b4209-profile_image-300x300.png',
                        'offline_image_url' => '',
                        'view_count' => 0,
                        'created_at' => '2018-09-04T15:23:04Z'
                    ]
                ]
            ]),
            'http_code' => 500
        ];
        $expectedResponse = ['error' => self::ERROR_GET_USERS_FAILED];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenResponse);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with('https://api.twitch.tv/helix/users?id=1234', [0 => 'Authorization: Bearer nrtovbe5h02os45krmjzvkt3hp74vf'])
            ->once()
            ->andReturn($getUserDataResponse);

        $returnedUserInfo = $this->userDataManager->getUserData('1234');

        $this->assertEquals($expectedResponse, $returnedUserInfo);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
