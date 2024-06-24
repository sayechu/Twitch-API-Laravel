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
use Tests\Builders\AnalyticsParameters;
use Tests\Builders\StreamerDataBuilder;
use Tests\Feature\GetFollowMessageTest;
use Tests\TestCase;

class FollowStreamerManagerTest extends TestCase
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;
    private FollowStreamerManager $followStreamerManager;
    private TokenProvider $tokenProvider;
    private array $headers;
    private array $body;
    private const GET_TOKEN_ERROR_MESSAGE = 'Acceso denegado debido a permisos insuficientes';
    private const NOT_FOUND_ERROR_MESSAGE = "El usuario (" . AnalyticsParameters::USERNAME . ") o el streamer ("
    . AnalyticsParameters::STREAMER_ID . ") especificado no existe en la API";

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
            'username' => AnalyticsParameters::USERNAME,
            'streamerId' => AnalyticsParameters::STREAMER_ID
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
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID)
            ->andThrows(InternalServerErrorException::class, GetFollowMessageTest::INTERNAL_SERVER_ERROR_MESSAGE);

        $this->expectException(InternalServerErrorException::class);
        $this->expectExceptionMessage(GetFollowMessageTest::INTERNAL_SERVER_ERROR_MESSAGE);

        $this->followStreamerManager->getFollowMessage(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID);
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
        $this->expectExceptionMessage(GetFollowMessageTest::CONFLICT_EXCEPTION_MESSAGE);

        $this->followStreamerManager->getFollowMessage(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID);
    }

    /**
     * @test
     */
    public function get_follow_message_returns_curl_token_error()
    {
        $getTokenResponse = [
            'response' => json_encode([
                'access_token' => AnalyticsParameters::TWITCH_TOKEN,
                'expires_in' => 5089418,
                'token_type' => 'bearer'
            ]),
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
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

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(self::GET_TOKEN_ERROR_MESSAGE);

        $this->followStreamerManager->getFollowMessage(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID);
    }

    /**
     * @test
     */
    public function get_follow_message_returns_curl_streamer_error()
    {
        $responseBuilder = (new StreamerDataBuilder())->withTestValues();
        $responseBuilder->withHttpCode(Response::HTTP_UNAUTHORIZED);
        $streamerResponse = $responseBuilder->build();

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
            ->once()
            ->andReturn($streamerResponse);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage(GetFollowMessageTest::GET_TOKEN_ERROR_MESSAGE);

        $this->followStreamerManager->getFollowMessage(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID);
    }

    /**
     * @test
     */
    public function get_follow_message_with_empty_streamer_data_returns_not_found_error()
    {
        $streamerResponse = [
            'response' => [
                'data' => []
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
            ->once()
            ->andReturn($streamerResponse);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(self::NOT_FOUND_ERROR_MESSAGE);

        $this->followStreamerManager->getFollowMessage(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID);
    }

    /**
     * @test
     */
    public function get_follow_message_returns_not_found_error()
    {
        $responseBuilder = (new StreamerDataBuilder())->withTestValues();
        $streamerResponse = $responseBuilder->build();

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
            ->once()
            ->andReturn($streamerResponse);
        $this->databaseClient
            ->expects('checkIfUsernameExists')
            ->once()
            ->andReturn(false);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(self::NOT_FOUND_ERROR_MESSAGE);

        $this->followStreamerManager->getFollowMessage(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID);
    }

    /**
     * @test
     */
    public function get_follow_message()
    {
        $expectedResponse = [
            "message" => "Ahora sigues a " . AnalyticsParameters::STREAMER_ID
        ];
        $responseBuilder = (new StreamerDataBuilder())->withTestValues();
        $streamerResponse = $responseBuilder->build();

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

        $returnedFollowMessage = $this->followStreamerManager->getFollowMessage(AnalyticsParameters::USERNAME, AnalyticsParameters::STREAMER_ID);
        $this->assertEquals($expectedResponse, $returnedFollowMessage);
    }
}

