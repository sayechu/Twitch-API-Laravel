<?php

namespace Tests\Unit\Services;

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
    private const ERROR_GET_TOKEN_FAILED = 'No se puede establecer conexión con Twitch en este momento';
    private const ERROR_GET_STREAMS_FAILED = 'No se pueden devolver streams en este momento, inténtalo más tarde';
    private const TWITCH_TOKEN = "nrtovbe5h02os45krmjzvkt3hp74vf";

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
    public function test_get_streams_data(): void
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
            ->once()
            ->andReturn($streamsResponse);

        $returnedStreams = $this->streamsDataManager->getStreamsData();

        $this->assertEquals($expectedResponse, $returnedStreams);
    }

    /**
     * @test
     */
    public function test_get_streams_data_with_token_error(): void
    {
        $expectedResponse = ['error' => self::ERROR_GET_TOKEN_FAILED];
        $tokenResponse = [
            "response" => null,
            "http_code" => 500
        ];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($tokenResponse);

        $returnedStreams = $this->streamsDataManager->getStreamsData();

        $this->assertEquals($expectedResponse, $returnedStreams);
    }

    /**
     * @test
     */
    public function test_get_streams_data_with_correct_token_but_curl_error(): void
    {
        $responseBuilder = (new StreamsDataBuilder())
            ->withTestValues()
            ->withHttpCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $streamsResponse = $responseBuilder->build();
        $expectedExceptionMessage = self::GET_STREAMS_ERROR_MESSAGE;

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($tokenResponse);
        $this->apiClient
            ->expects('makeCurlCall')
            ->once()
            ->andReturn($streamsResponse);

        $returnedStreams = $this->streamsDataManager->getStreamsData();

        $this->assertEquals($expectedResponse, $returnedStreams);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
