<?php

namespace Tests\Unit\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\InternalServerErrorException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Services\ApiClient;
use App\Services\DBClient;
use App\Services\FollowStreamerManager;
use App\Services\TokenProvider;
use Illuminate\Http\Response;
use Mockery;
use Tests\TestCase;

class FollowStreamerManagerTest extends TestCase
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;
    private FollowStreamerManager $followStreamerManager;
    private TokenProvider $tokenProvider;
    private array $headers;
    private array $body;
    private const TWITCH_TOKEN = 'nrtovbe5h02os45krmjzvkt3hp74vf';
    private const USERNAME = "username";
    private const STREAMER_ID = '1234';
    private const GET_TOKEN_ERROR_MESSAGE = 'Acceso denegado debido a permisos insuficientes';
    private const GET_STREAMER_ERROR_MESSAGE = 'Token de autenticación no proporcionado o inválido';
    private const CONFLICT_EXCEPTION_MESSAGE = 'El usuario ya está siguiendo al streamer';
    private const INTERNAL_SERVER_ERROR_MESSAGE = "Error del servidor al seguir al streamer";
    private const NOT_FOUND_ERROR_MESSAGE = "El usuario (" . self::USERNAME . ") o el streamer ("
                                             . self::STREAMER_ID . ") especificado no existe en la API";

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->databaseClient = Mockery::mock(DBClient::class);
        $this->tokenProvider = new TokenProvider($this->apiClient, $this->databaseClient);
        $this->followStreamerManager = new FollowStreamerManager($this->tokenProvider, $this->apiClient, $this->databaseClient);
        $this->app
            ->when(FollowStreamerManager::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
        $this->app
            ->when(FollowStreamerManager::class)
            ->needs(DBClient::class)
            ->give(fn() => $this->databaseClient);
        $this->app
            ->when(TokenProvider::class)
            ->needs(DBClient::class)
            ->give(fn() => $this->databaseClient);
        $this->app
            ->when(TokenProvider::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
        $this->headers = ['Content-Type' => 'application/json'];
        $this->body = [
            'username' => self::USERNAME,
            'streamerId' => self::STREAMER_ID
        ];
    }

    /**
     * @test
     */
    public function get_follow_message_in_users_follows_streamer_returns_internal_server_error()
    {
        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->with(self::USERNAME, self::STREAMER_ID)
            ->andThrows(InternalServerErrorException::class, self::INTERNAL_SERVER_ERROR_MESSAGE);

        $this->expectException(InternalServerErrorException::class);
        $this->expectExceptionMessage(self::INTERNAL_SERVER_ERROR_MESSAGE);

        $this->followStreamerManager->getFollowMessage(self::USERNAME, self::STREAMER_ID);
    }

    /**
     * @test
     */
    public function get_follow_message_returns_conflict_error()
    {
        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->andReturn(true);

        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage(self::CONFLICT_EXCEPTION_MESSAGE);

        $this->followStreamerManager->getFollowMessage(self::USERNAME, self::STREAMER_ID);
    }

    /**
     * @test
     */
    public function get_follow_message_returns_curl_token_error()
    {
        $getTokenResponse = [
            'response' => json_encode([
                'access_token' => self::TWITCH_TOKEN,
                'expires_in' => 5089418,
                'token_type' => 'bearer'
            ]),
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->with(self::USERNAME, self::STREAMER_ID)
            ->andReturn(false);
        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(false);
        $this->apiClient
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenResponse);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(self::GET_TOKEN_ERROR_MESSAGE);

        $this->followStreamerManager->getFollowMessage(self::USERNAME, self::STREAMER_ID);
    }

    /**
     * @test
     */
    public function get_follow_message_returns_curl_streamer_error()
    {
        $streamerResponse = [
            'response' => json_encode([
                'data' => [
                    [
                        'id' => self::STREAMER_ID,
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
            'http_code' => Response::HTTP_UNAUTHORIZED
        ];

        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->with(self::USERNAME, self::STREAMER_ID)
            ->andReturn(false);
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
            ->andReturn($streamerResponse);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage(self::GET_STREAMER_ERROR_MESSAGE);

        $this->followStreamerManager->getFollowMessage(self::USERNAME, self::STREAMER_ID);
    }

    /**
     * @test
     */
    public function get_follow_message_with_empty_streamer_data_returns_not_found_error()
    {
        $streamerResponse = [
            'response' => json_encode([
                'data' => []
            ]),
            'http_code' => Response::HTTP_OK
        ];

        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->with(self::USERNAME, self::STREAMER_ID)
            ->andReturn(false);
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
            ->andReturn($streamerResponse);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(self::NOT_FOUND_ERROR_MESSAGE);

        $this->followStreamerManager->getFollowMessage(self::USERNAME, self::STREAMER_ID);
    }

    /**
     * @test
     */
    public function get_follow_message_returns_not_found_error()
    {
        $streamerResponse = [
            'response' => json_encode([
                'data' => [
                    [
                        'id' => self::STREAMER_ID,
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
            ->expects('userFollowsStreamer')
            ->once()
            ->with(self::USERNAME, self::STREAMER_ID)
            ->andReturn(false);
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
            ->andReturn($streamerResponse);
        $this->databaseClient
            ->expects('checkIfUsernameExists')
            ->once()
            ->andReturn(false);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(self::NOT_FOUND_ERROR_MESSAGE);

        $this->followStreamerManager->getFollowMessage(self::USERNAME, self::STREAMER_ID);
    }

    /**
     * @test
     */
    public function get_follow_message()
    {
        $expectedResponse = [
            "message" => "Ahora sigues a " . self::STREAMER_ID
        ];
        $streamerResponse = [
            'response' => json_encode([
                'data' => [
                    [
                        'id' => self::STREAMER_ID,
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
            ->expects('userFollowsStreamer')
            ->once()
            ->with(self::USERNAME, self::STREAMER_ID)
            ->andReturn(false);
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
            ->andReturn($streamerResponse);
        $this->databaseClient
            ->expects('checkIfUsernameExists')
            ->once()
            ->andReturn(true);
        $this->databaseClient
            ->expects('addUserFollowsStreamer')
            ->once()
            ->with(self::USERNAME, self::STREAMER_ID);

        $returnedFollowMessage = $this->followStreamerManager->getFollowMessage(self::USERNAME, self::STREAMER_ID);
        $this->assertEquals($expectedResponse, $returnedFollowMessage);
    }
}

