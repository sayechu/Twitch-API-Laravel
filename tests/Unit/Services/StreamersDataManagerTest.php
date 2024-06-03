<?php

namespace Tests\Unit\Services;

use App\Services\StreamersDataManager;
use Tests\Builders\StreamerDataBuilder;
use App\Services\TokenProvider;
use Illuminate\Http\Response;
use App\Services\ApiClient;
use Tests\TestCase;
use Exception;
use Mockery;

class StreamersDataManagerTest extends TestCase
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;
    private StreamersDataManager $userDataManager;

    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    private const GET_USERS_ERROR_MESSAGE = 'No se pueden devolver usuarios en este momento, inténtalo más tarde';
    private const TWITCH_TOKEN = 'nrtovbe5h02os45krmjzvkt3hp74vf';
    private const GET_USERS_URLS = 'https://api.twitch.tv/helix/users';

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenProvider = Mockery::mock(TokenProvider::class);
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->userDataManager = new StreamersDataManager($this->tokenProvider, $this->apiClient);

        $this->app
            ->when(StreamersDataManager::class)
            ->needs(TokenProvider::class)
            ->give(fn() => $this->tokenProvider);
        $this->app
            ->when(StreamersDataManager::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
    }

    /**
     * @test
     */
    public function get_user_data(): void
    {
        $responseBuilder = (new StreamerDataBuilder())->withTestValues();
        $getUserDataResponse = $responseBuilder->build();
        $expectedGetUserDataResponse = $responseBuilder->buildExpected();

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn(self::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(self::GET_USERS_URLS . '?id=1234', [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($getUserDataResponse);

        $returnedUserInfo = $this->userDataManager->getUserData('1234');

        $this->assertEquals($expectedGetUserDataResponse, $returnedUserInfo);
    }

    /**
     * @test
     */
    public function get_user_data_token_passes_curl_call_fails(): void
    {
        $responseBuilder = (new StreamerDataBuilder())
            ->withTestValues()
            ->withHttpCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $getUserDataResponse = $responseBuilder->build();
        $expectedResponse = self::GET_USERS_ERROR_MESSAGE;

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn(self::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(self::GET_USERS_URLS . '?id=1234', [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($getUserDataResponse);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedResponse);

        $this->userDataManager->getUserData('1234');
    }

    /**
     * @test
     */
    public function get_user_data_token_failure(): void
    {
        $getTokenResponse = [
            'response' => json_encode([
                'access_token' => self::TWITCH_TOKEN,
                'expires_in' => 5089418,
                'token_type' => 'bearer'
            ]),
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];
        $expectedResponse = self::GET_TOKEN_ERROR_MESSAGE;

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenResponse);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedResponse);

        $this->userDataManager->getUserData('1234');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
