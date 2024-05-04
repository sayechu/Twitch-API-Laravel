<?php

namespace Tests\Unit\Services;

use App\Services\GetStreamsService;
use App\Services\StreamsManager;
use Mockery;
use Tests\TestCase;

class GetStreamsServiceTests extends TestCase
{
    /**
     * @test
     */
    public function get_streams_test(): void
    {
        $streamsManager = Mockery::mock(StreamsManager::class);
        $getStreamsService = new GetStreamsService($streamsManager);
        $this->app
            ->when(GetStreamsService::class)
            ->needs(StreamsManager::class)
            ->give(fn() => $streamsManager);
        $expectedStreams = [
            [
                'id' => 'Stream Id 1',
                'user_id' => 'User Id 1',
                'user_login' => 'User Login 1',
                'user_name' => 'User Name 1',
                'game_id' => '21779',
                'game_name' => 'Game Name',
                'title' => 'Stream Title 1',
                'user_name' => 'User Name 1'
            ],
            [
                'id' => 'Stream Id 2',
                'user_id' => 'User Id 2',
                'user_login' => 'User Login 2',
                'user_name' => 'User Name 2',
                'game_id' => '21779',
                'game_name' => 'Game Name',
                'title' => 'Stream Title 2',
                'user_name' => 'User Name 2'
            ]
        ];
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

        $streamsManager
            ->expects('getStreams')
            ->once()
            ->andReturn($expectedStreams);

        $returnedStreams = $getStreamsService->getStreams();

        $this->assertEquals($expectedFilteredStreams, $returnedStreams);
    }
    protected function tearDown(): void
    {
        Mockery::close();
    }
}
