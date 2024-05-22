<?php

namespace Tests\Unit\Services;

use Illuminate\Http\Response;
use App\Services\ApiClient;
use App\Services\TokenProvider;
use App\Services\StreamersDataManager;
use Tests\Builders\StreamerDTO;
use Tests\Builders\StreamerDTOBuilder;
use Mockery;
use Tests\TestCase;


class StreamersDataManagerTest extends TestCase
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;
    private StreamersDataManager $streamerDataManager;
    private StreamerDTO $expectedStreamerDTO;

    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    private const GET_STREAMERS_ERROR_MESSAGE = 'No se pueden devolver usuarios en este momento, inténtalo más tarde';
    private const TWITCH_TOKEN = 'nrtovbe5h02os45krmjzvkt3hp74vf';
    private const GET_STREAMERS_URLS = 'https://api.twitch.tv/helix/users';

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenProvider = Mockery::mock(TokenProvider::class);
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->streamerDataManager = new StreamersDataManager($this->tokenProvider, $this->apiClient);

        $this->app
            ->when(StreamersDataManager::class)
            ->needs(TokenProvider::class)
            ->give(fn() => $this->tokenProvider);
        $this->app
            ->when(StreamersDataManager::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
        $this->expectedStreamerDTO = (new StreamerDTOBuilder())
            ->withId('UserId')
            ->withLogin('userLogin')
            ->withDisplayName('displayName')
            ->withType('type')
            ->withBroadcasterType('BroadcasterType')
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
    public function get_streamer_data(): void
    {
        $getStreamerDataResponse = [
            'response' => json_encode([
                'data' => [$this->expectedStreamerDTO->toArray()]
            ]),
            'http_code' => Response::HTTP_OK
        ];
        $expectedGetStreamerDataResponse = [
            "data" => [$this->expectedStreamerDTO->toArray()]
        ];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn(self::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(self::GET_STREAMERS_URLS . '?id=1234', [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($getStreamerDataResponse);

        $returnedStreamerInfo = $this->streamerDataManager->getStreamerData('1234');

        $this->assertEquals($expectedGetStreamerDataResponse, $returnedStreamerInfo);
    }

    /**
     * @test
     */
    public function get_streamer_data_token_failure(): void
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
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedResponse);

        $this->streamerDataManager->getStreamerData('1234');
    }

    /**
     * @test
     */
    public function get_user_data_token_passes_curl_call_fails(): void
    {
        $getUserDataResponse = [
            'response' => json_encode([
                'data' => [$this->expectedStreamerDTO->toArray()]
            ]),
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];
        $expectedResponse = self::GET_STREAMERS_ERROR_MESSAGE;

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn(self::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(self::GET_STREAMERS_URLS . '?id=1234', [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($getUserDataResponse);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedResponse);

        $this->streamerDataManager->getStreamerData('1234');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
