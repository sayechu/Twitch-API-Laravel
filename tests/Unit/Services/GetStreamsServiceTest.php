<?php

namespace Tests\Unit\Services;

use App\Services\GetStreamsService;
use App\Services\StreamsDataManager;
use Tests\Builders\StreamDTO;
use Tests\Builders\StreamDTOBuilder;
use Mockery;
use Tests\TestCase;

class GetStreamsServiceTest extends TestCase
{
    private GetStreamsService $getStreamsService;
    private StreamsDataManager $streamsDataManager;
    private StreamDTO $expectedStream1;
    private StreamDTO $expectedStream2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->streamsDataManager = Mockery::mock(StreamsDataManager::class);
        $this->getStreamsService = new GetStreamsService($this->streamsDataManager);
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
     */
    public function execute_returns_streams_data(): void
    {
        $streamsResponse = [$this->expectedStream1->toArray(), $this->expectedStream2->toArray()];

        $expectedFilteredStreams = [
            [
                'title' => 'Stream Title 1',
                'user_name' => 'User Name 1'
            ],
            [
                'title' => 'Stream Title 2',
                'user_name' => 'User Name 2'
            ]
        ];

        $this->streamsDataManager
            ->expects('getStreamsData')
            ->once()
            ->andReturn($streamsResponse);

        $returnedStreams = $this->getStreamsService->execute();

        $this->assertEquals($expectedFilteredStreams, $returnedStreams);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
