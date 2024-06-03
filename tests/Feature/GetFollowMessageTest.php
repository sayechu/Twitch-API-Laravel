<?php

namespace Tests\Feature;

use App\Exceptions\InternalServerErrorException;
use App\Services\FollowStreamerManager;
use Tests\Builders\AnalyticsParameters;
use Tests\TestCase;
use Illuminate\Http\Response;
use App\Services\ApiClient;
use App\Services\DBClient;
use App\Services\TokenProvider;
use Mockery;

class GetFollowMessageTest extends TestCase
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;
    private array $headers;
    private array $body;
    public const GET_TOKEN_ERROR_MESSAGE = 'Token de autenticación no proporcionado o inválido';
    public const CONFLICT_EXCEPTION_MESSAGE = 'El usuario ya está siguiendo al streamer';
    public const INTERNAL_SERVER_ERROR_MESSAGE = "Error del servidor al seguir al streamer";

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->databaseClient = Mockery::mock(DBClient::class);
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
            'username' => AnalyticsParameters::USERNAME,
            'streamerId' => AnalyticsParameters::STREAMER_ID
        ];
    }

    /**
     * @test
     */
    public function get_follow_message_with_user_following_streamer_returns_conflict_error()
    {
        $expectedResponse = [
            "error" => self::CONFLICT_EXCEPTION_MESSAGE
        ];

        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->andReturn(true);

        $followResponse = $this->json('POST', '/analytics/follow', $this->body, $this->headers);

        $followResponse->assertStatus(Response::HTTP_CONFLICT);
        $followResponse->assertExactJson($expectedResponse);
    }

    /**
     * @test
     */
    public function get_follow_message_with_token_stored_returns_follow_confirmation_message()
    {
        $expectedResponse = [
            "message" => "Ahora sigues a " . AnalyticsParameters::STREAMER_ID
        ];
        $streamerResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => AnalyticsParameters::STREAMER_ID,
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

        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID)
            ->andReturn(false);
        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(true);
        $this->databaseClient
            ->expects('getToken')
            ->once()
            ->andReturn(AnalyticsParameters::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(AnalyticsParameters::ANALYTICS_GET_USERS_URL . '?id=' . AnalyticsParameters::STREAMER_ID, [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->once()
            ->andReturn($streamerResponse);
        $this->databaseClient
            ->expects('checkIfUsernameExists')
            ->once()
            ->andReturn(true);
        $this->databaseClient
            ->expects('addUserFollowsStreamer')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID);

        $followResponse = $this->json('POST', '/analytics/follow', $this->body, $this->headers);

        $followResponse->assertStatus(Response::HTTP_OK);
        $followResponse->assertExactJson($expectedResponse);
    }

    /**
     * @test
     */
    public function get_follow_message_without_token_stored_returns_follow_confirmation_message()
    {
        $expectedResponse = [
            "message" => "Ahora sigues a " . AnalyticsParameters::STREAMER_ID
        ];
        $streamerResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => AnalyticsParameters::STREAMER_ID,
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
        $getTokenResponse = [
            'response' => json_encode([
                'access_token' => AnalyticsParameters::TWITCH_TOKEN,
                'expires_in' => 5089418,
                'token_type' => 'bearer'
            ]),
            'http_code' => Response::HTTP_OK
        ];

        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID)
            ->andReturn(false);
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
            ->with(AnalyticsParameters::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(AnalyticsParameters::ANALYTICS_GET_USERS_URL . '?id=' . AnalyticsParameters::STREAMER_ID, [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->once()
            ->andReturn($streamerResponse);
        $this->databaseClient
            ->expects('checkIfUsernameExists')
            ->once()
            ->andReturn(true);
        $this->databaseClient
            ->expects('addUserFollowsStreamer')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID);

        $followResponse = $this->json('POST', '/analytics/follow', $this->body, $this->headers);

        $followResponse->assertStatus(Response::HTTP_OK);
        $followResponse->assertExactJson($expectedResponse);
    }

    /**
     * @test
     */
    public function get_follow_message_returns_token_invalid()
    {
        $expectedResponse = [
            "error" => self::GET_TOKEN_ERROR_MESSAGE
        ];
        $streamerResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => AnalyticsParameters::STREAMER_ID,
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
            'http_code' => Response::HTTP_UNAUTHORIZED
        ];

        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID)
            ->andReturn(false);
        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(true);
        $this->databaseClient
            ->expects('getToken')
            ->once()
            ->andReturn(AnalyticsParameters::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(AnalyticsParameters::ANALYTICS_GET_USERS_URL . '?id=' . AnalyticsParameters::STREAMER_ID, [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->once()
            ->andReturn($streamerResponse);

        $followResponse = $this->json('POST', '/analytics/follow', $this->body, $this->headers);

        $followResponse->assertStatus(Response::HTTP_UNAUTHORIZED);
        $followResponse->assertExactJson($expectedResponse);
    }

    /**
     * @test
     */
    public function get_follow_message_with_streamer_data_empty_returns_streamer_not_found()
    {
        $expectedResponse = [
            "error" => "El usuario (" . AnalyticsParameters::USERNAME . ") o el streamer ("
                . AnalyticsParameters::STREAMER_ID . ") especificado no existe en la API"
        ];
        $streamerResponse = [
            'response' => [
                'data' => []
            ],
            'http_code' => Response::HTTP_OK
        ];
        $getTokenResponse = [
            'response' => json_encode([
                'access_token' => AnalyticsParameters::TWITCH_TOKEN,
                'expires_in' => 5089418,
                'token_type' => 'bearer'
            ]),
            'http_code' => Response::HTTP_OK
        ];

        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID)
            ->andReturn(false);
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
            ->with(AnalyticsParameters::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(AnalyticsParameters::ANALYTICS_GET_USERS_URL . '?id=' . AnalyticsParameters::STREAMER_ID, [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->once()
            ->andReturn($streamerResponse);

        $followResponse = $this->json('POST', '/analytics/follow', $this->body, $this->headers);

        $followResponse->assertStatus(Response::HTTP_NOT_FOUND);
        $followResponse->assertExactJson($expectedResponse);
    }

    /**
     * @test
     */
    public function get_follow_message_without_existing_user_returns_user_not_found()
    {
        $expectedResponse = [
            "error" => "El usuario (" . AnalyticsParameters::USERNAME . ") o el streamer ("
                . AnalyticsParameters::STREAMER_ID . ") especificado no existe en la API"
        ];
        $streamerResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => AnalyticsParameters::STREAMER_ID,
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
        $getTokenResponse = [
            'response' => json_encode([
                'access_token' => AnalyticsParameters::TWITCH_TOKEN,
                'expires_in' => 5089418,
                'token_type' => 'bearer'
            ]),
            'http_code' => Response::HTTP_OK
        ];

        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID)
            ->andReturn(false);
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
            ->with(AnalyticsParameters::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(AnalyticsParameters::ANALYTICS_GET_USERS_URL . '?id=' . AnalyticsParameters::STREAMER_ID, [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->once()
            ->andReturn($streamerResponse);
        $this->databaseClient
            ->expects('checkIfUsernameExists')
            ->once()
            ->andReturn(false);

        $followResponse = $this->json('POST', '/analytics/follow', $this->body, $this->headers);

        $followResponse->assertStatus(Response::HTTP_NOT_FOUND);
        $followResponse->assertExactJson($expectedResponse);
    }

    /**
     * @test
     */
    public function get_follow_message_in_user_follows_streamer_returns_internal_server_error()
    {
        $expectedResponse = [
            "error" => self::INTERNAL_SERVER_ERROR_MESSAGE
        ];

        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID)
            ->andThrows(InternalServerErrorException::class, self::INTERNAL_SERVER_ERROR_MESSAGE);

        $followResponse = $this->json('POST', '/analytics/follow', $this->body, $this->headers);

        $followResponse->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $followResponse->assertExactJson($expectedResponse);
    }

    /**
     * @test
     */
    public function get_follow_message_in_check_if_username_exists_returns_internal_server_error()
    {
        $expectedResponse = [
            "error" => self::INTERNAL_SERVER_ERROR_MESSAGE
        ];
        $streamerResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => AnalyticsParameters::STREAMER_ID,
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
        $getTokenResponse = [
            'response' => json_encode([
                'access_token' => AnalyticsParameters::TWITCH_TOKEN,
                'expires_in' => 5089418,
                'token_type' => 'bearer'
            ]),
            'http_code' => Response::HTTP_OK
        ];

        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID)
            ->andReturn(false);
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
            ->with(AnalyticsParameters::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(AnalyticsParameters::ANALYTICS_GET_USERS_URL . '?id=' . AnalyticsParameters::STREAMER_ID, [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->once()
            ->andReturn($streamerResponse);
        $this->databaseClient
            ->expects('checkIfUsernameExists')
            ->once()
            ->andThrows(InternalServerErrorException::class, self::INTERNAL_SERVER_ERROR_MESSAGE);

        $followResponse = $this->json('POST', '/analytics/follow', $this->body, $this->headers);

        $followResponse->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $followResponse->assertExactJson($expectedResponse);
    }

    /**
     * @test
     */
    public function get_follow_message_in_add_user_follows_streamer_returns_internal_server_error()
    {
        $expectedResponse = [
            "error" => self::INTERNAL_SERVER_ERROR_MESSAGE
        ];
        $streamerResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => AnalyticsParameters::STREAMER_ID,
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

        $this->databaseClient
            ->expects('userFollowsStreamer')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID)
            ->andReturn(false);
        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(true);
        $this->databaseClient
            ->expects('getToken')
            ->once()
            ->andReturn(AnalyticsParameters::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(AnalyticsParameters::ANALYTICS_GET_USERS_URL . '?id=' . AnalyticsParameters::STREAMER_ID, [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->once()
            ->andReturn($streamerResponse);
        $this->databaseClient
            ->expects('checkIfUsernameExists')
            ->once()
            ->andReturn(true);
        $this->databaseClient
            ->expects('addUserFollowsStreamer')
            ->once()
            ->andThrows(InternalServerErrorException::class, self::INTERNAL_SERVER_ERROR_MESSAGE);

        $followResponse = $this->json('POST', '/analytics/follow', $this->body, $this->headers);

        $followResponse->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $followResponse->assertExactJson($expectedResponse);
    }

}
