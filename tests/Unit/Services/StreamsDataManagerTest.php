<?php

namespace Tests\Unit\Services;

use App\Services\ApiClient;
use App\Services\StreamsDataManager;
use App\Services\TokenProvider;
use Tests\Builders\StreamDTO;
use Tests\Builders\StreamDTOBuilder;
use Illuminate\Http\Response;
use Mockery;
use Tests\TestCase;

class StreamsDataManagerTest extends TestCase
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;
    private StreamsDataManager $streamsDataManager;
    private StreamDTO $expectedStream1;
    private StreamDTO $expectedStream2;
    private const GET_TOKEN_ERROR_MESSAGE = 'No se puede establecer conexión con Twitch en este momento';
    private const GET_STREAMS_ERROR_MESSAGE = 'No se pueden devolver streams en este momento, inténtalo más tarde';
    private const TWITCH_TOKEN = "nrtovbe5h02os45krmjzvkt3hp74vf";
    private const GET_STREAMS_URL = 'https://api.twitch.tv/helix/streams';

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenProvider = Mockery::mock(TokenProvider::class);
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->streamsDataManager = new StreamsDataManager($this->tokenProvider, $this->apiClient);
        $this->expectedStream1 = (new StreamDTOBuilder())
            ->withId('40627613557')
            ->withUserId('92038375')
            ->withUserLogin('caedrel')
            ->withUserName('User Name 1')
            ->withGameId('21779')
            ->withGameName('League of Legends')
            ->withType('live')
            ->withTitle('Stream Title 1')
            ->withViewerCount(46181)
            ->withStartedAt('2024-05-08T07:35:07Z')
            ->withLanguage('en')
            ->withThumbnailUrl('https://static-cdn.jtvnw.net/previews-ttv/live_user_caedrel-{width}x{height}.jpg')
            ->withTags(['xdd', 'Washed', 'degen', 'English', 'adhd', 'vtuber', 'Ratking', 'LPL', 'LCK', 'LEC'])
            ->withIsMature(false)
            ->build();
        $this->expectedStream2 = (new StreamDTOBuilder())
            ->withId('40627613557')
            ->withUserId('92038375')
            ->withUserLogin('caedrel')
            ->withUserName('User Name 2')
            ->withGameId('21779')
            ->withGameName('League of Legends')
            ->withType('live')
            ->withTitle('Stream Title 2')
            ->withViewerCount(46181)
            ->withStartedAt('2024-05-08T07:35:07Z')
            ->withLanguage('en')
            ->withThumbnailUrl('https://static-cdn.jtvnw.net/previews-ttv/live_user_caedrel-{width}x{height}.jpg')
            ->withTags(['xdd', 'Washed', 'degen', 'English', 'adhd', 'vtuber', 'Ratking', 'LPL', 'LCK', 'LEC'])
            ->withIsMature(false)
            ->build();
    }

    /**
     * @test
     * @throws \Exception
     */
    public function get_streams_data_returns_streams_data(): void
    {
        $tokenResponse = self::TWITCH_TOKEN;
        $curlCallResponse = [
            'response' => json_encode([
                'data' => [$this->expectedStream1->toArray(), $this->expectedStream2->toArray()]
            ]),
            'http_code' => Response::HTTP_OK
        ];
        $expectedResponse = [$this->expectedStream1->toArray(), $this->expectedStream2->toArray()];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($tokenResponse);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(self::GET_STREAMS_URL, [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($curlCallResponse);

        $returnedStreams = $this->streamsDataManager->getStreamsData();

        $this->assertEquals($expectedResponse, $returnedStreams);
    }

    /**
     * @test
     */
    public function get_streams_data_returns_token_error(): void
    {
        $expectedExceptionMessage = self::GET_TOKEN_ERROR_MESSAGE;
        $tokenResponse = [
            "response" => null,
            "http_code" => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($tokenResponse);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->streamsDataManager->getStreamsData();
    }

    /**
     * @test
     */
    public function get_streams_data_returns_streams_curl_error(): void
    {
        $streamsResponse = [
            'response' => json_encode([
                'data' => [$this->expectedStream1->toArray(), $this->expectedStream2->toArray()]
            ]),
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];
        $expectedExceptionMessage = self::GET_STREAMS_ERROR_MESSAGE;


        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn(self::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(self::GET_STREAMS_URL, [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($streamsResponse);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->streamsDataManager->getStreamsData();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
