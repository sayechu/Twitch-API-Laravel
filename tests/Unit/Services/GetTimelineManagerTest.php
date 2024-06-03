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
    private $tokenProvider;
    private $streamersProvider;
    private $streamsProvider;
    private $apiClient;
    private $dbClient;
    private $getTimelineManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->dbClient = Mockery::mock(DBClient::class);

        // Mock the TokenProvider instead of creating a new instance
        $this->tokenProvider = Mockery::mock(TokenProvider::class, [$this->apiClient, $this->dbClient])->makePartial();

        $this->streamersProvider = Mockery::mock(TimelineStreamersProvider::class);
        $this->streamsProvider = Mockery::mock(TimelineStreamsProvider::class);

        $this->getTimelineManager = new GetTimelineManager(
            $this->tokenProvider,
            $this->streamersProvider,
            $this->streamsProvider
        );
    }


    public function testGetStreamersTimelineThrowsInternalServerError()
    {
        $username = 'ibai';
        $followingStreamers = ['streamer1', 'streamer2'];

        $this->streamersProvider
            ->shouldReceive('getTimelineStreamers')
            ->with($username)
            ->andReturn($followingStreamers);

        $this->tokenProvider
            ->shouldReceive('getToken')
            ->once()
            ->andThrow(new InternalServerErrorException(" Error del servidor al obtener el timeline."));

        $this->expectException(InternalServerErrorException::class);
        $this->expectExceptionMessage("Error del servidor al obtener el timeline.");

        $this->getTimelineManager->getStreamersTimeline($username);
    }

    public function testGetStreamersTimelineThrowsNotFoundException()
    {
        $username = 'nonexistentuser';

        $this->streamersProvider
            ->shouldReceive('getTimelineStreamers')
            ->with($username)
            ->once()
            ->andThrow(new NotFoundException("El usuario especificado ( {$username} ) no existe."));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("El usuario especificado ( {$username} ) no existe.");

        $this->getTimelineManager->getStreamersTimeline($username);
    }



    public function testGetStreamersTimelineSuccess()
    {
        $username = 'validuser';
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
            ->with($username)
            ->andReturn($followingStreamers);

        $this->streamsProvider
            ->shouldReceive('getTimelineStreams')
            ->with('validToken', $followingStreamers)
            ->andReturn($expectedStreams);

        $result = $this->getTimelineManager->getStreamersTimeline($username);

        $this->assertEquals($expectedStreams, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
