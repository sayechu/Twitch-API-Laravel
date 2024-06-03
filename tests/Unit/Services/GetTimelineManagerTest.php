<?php
namespace Tests\Unit\Services;

use App\Services\GetTimelineManager;
use App\Services\TokenProvider;
use App\Services\TimelineStreamersProvider;
use App\Services\TimelineStreamsProvider;
use App\Services\ApiClient;
use App\Services\DBClient;
use App\Exceptions\NotFoundException;
use App\Exceptions\InternalServerErrorException;
use Mockery;
use Tests\TestCase;
use Illuminate\Http\Response;

class GetTimelineManagerTest extends TestCase
{
    private TokenProvider$tokenProvider;
    private TimelineStreamersProvider $streamersProvider;
    private TimelineStreamsProvider $streamsProvider;
    private ApiClient $apiClient;
    private DBClient $dbClient;
    private GetTimelineManager $getTimelineManager;
    private const USERNAME = "username";
    private const NOT_FOUND_ERROR_MESSAGE = "El usuario especificado ( " . self::USERNAME . " ) no existe.";
    private const INTERNAL_SERVER_ERROR_MESSAGE = "Error del servidor al obtener el timeline.";

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->dbClient = Mockery::mock(DBClient::class);

        $this->tokenProvider = Mockery::mock(TokenProvider::class, [$this->apiClient, $this->dbClient])->makePartial();

        $this->streamersProvider = Mockery::mock(TimelineStreamersProvider::class);
        $this->streamsProvider = Mockery::mock(TimelineStreamsProvider::class);

        $this->getTimelineManager = new GetTimelineManager(
            $this->tokenProvider,
            $this->streamersProvider,
            $this->streamsProvider
        );
    }

    /**
     * @test
     */
    public function get_streamers_timeline_returns_internal_server_error()
    {
        $followingStreamers = ['streamer1', 'streamer2'];

        $this->streamersProvider
            ->shouldReceive('getTimelineStreamers')
            ->with(self::USERNAME)
            ->andReturn($followingStreamers);

        $this->tokenProvider
            ->shouldReceive('getToken')
            ->once()
            ->andThrow(new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_MESSAGE));

        $this->expectException(InternalServerErrorException::class);
        $this->expectExceptionMessage(self::INTERNAL_SERVER_ERROR_MESSAGE);

        $this->getTimelineManager->getStreamersTimeline(self::USERNAME);
    }

    /**
     * @test
     */
    public function get_streamers_timeline_returns_not_found_exception()
    {
        $this->streamersProvider
            ->shouldReceive('getTimelineStreamers')
            ->with(self::USERNAME)
            ->once()
            ->andThrow(new NotFoundException(self::NOT_FOUND_ERROR_MESSAGE));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(self::NOT_FOUND_ERROR_MESSAGE);

        $this->getTimelineManager->getStreamersTimeline(self::USERNAME);
    }

    /**
     * @test
     */
    public function get_streamers_timeline()
    {
        $followingStreamers = ['streamer1', 'streamer2'];
        $twitchTokenResponse = ['response' => json_encode(['access_token' => 'validToken'])];
        $expectedStreams = [
            ['streamId' => 'stream1', 'title' => 'First Stream'],
            ['streamId' => 'stream2', 'title' => 'Second Stream']
        ];

        $this->dbClient
            ->shouldReceive('isTokenStoredInDatabase')
            ->andReturn(false);

        $this->apiClient
            ->shouldReceive('getToken')
            ->andReturn($twitchTokenResponse);

        $this->dbClient
            ->shouldReceive('storeToken')
            ->with('validToken');

        $this->streamersProvider
            ->shouldReceive('getTimelineStreamers')
            ->with(self::USERNAME)
            ->andReturn($followingStreamers);

        $this->streamsProvider
            ->shouldReceive('getTimelineStreams')
            ->with('validToken', $followingStreamers)
            ->andReturn($expectedStreams);

        $result = $this->getTimelineManager->getStreamersTimeline(self::USERNAME);

        $this->assertEquals($expectedStreams, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
