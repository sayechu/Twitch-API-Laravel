<?php

namespace Tests\Unit\Services;

use Illuminate\Http\Response;
use App\Services\ApiClient;
use App\Services\StreamsDataManager;
use App\Services\TokenProvider;
use Mockery;
use Tests\Builders\StreamsDataBuilder;
use Tests\TestCase;

class StreamsDataManagerTest extends TestCase
{
    private TokenProvider $tokenProvider;
    private ApiClient $apiClient;
    private StreamsDataManager $streamsDataManager;
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
    }

    /**
     * @test
     */
    public function get_streams_data_returns_streams_data(): void
    {
        $responseBuilder = (new StreamsDataBuilder())->withTestValues();
        $streamsResponse = $responseBuilder->build();
        $expectedResponse = $responseBuilder->buildExpected();

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn(self::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(self::GET_STREAMS_URL, [0 => 'Authorization: Bearer ' . self::TWITCH_TOKEN])
            ->once()
            ->andReturn($streamsResponse);

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
        $responseBuilder = (new StreamsDataBuilder())
            ->withTestValues()
            ->withHttpCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $streamsResponse = $responseBuilder->build();
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
