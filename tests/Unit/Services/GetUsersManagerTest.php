<?php

namespace Tests\Unit\Services;

use App\Exceptions\InternalServerErrorException;
use App\Services\DBClient;
use App\Services\GetUsersManager;
use Mockery;
use Tests\Feature\GetUsersTest;
use Tests\TestCase;

class GetUsersManagerTest extends TestCase
{
    private DBClient $databaseClient;
    private GetUsersManager $getUsersManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseClient = Mockery::mock(DBClient::class);
        $this->getUsersManager = new GetUsersManager($this->databaseClient);
    }

    /**
     * @test
     */
    public function get_users_and_streamers(): void
    {
        $users = [
            ['username' => 'user1'],
            ['username' => 'user2']
        ];
        $streamersUser1 = ['streamer1', 'streamer2'];
        $streamersUser2 = ['streamer3', 'streamer4'];
        $expectedResponse = [
            ['username' => 'user1', 'followedStreamers' => $streamersUser1],
            ['username' => 'user2', 'followedStreamers' => $streamersUser2]
        ];

        $this->databaseClient
            ->expects('getUsers')
            ->once()
            ->andReturn($users);
        $this->databaseClient
            ->expects('getStreamers')
            ->with('user1')
            ->once()
            ->andReturn($streamersUser1);
        $this->databaseClient
            ->expects('getStreamers')
            ->with('user2')
            ->once()
            ->andReturn($streamersUser2);

        $actualResponse = $this->getUsersManager->getUsersAndStreamers();

        $this->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * @test
     */
    public function get_users_and_streamers_returns_database_error(): void
    {
        $this->databaseClient
            ->expects('getUsers')
            ->once()
            ->andThrow(new InternalServerErrorException(GetUsersTest::INTERNAL_SERVER_ERROR_MESSAGE));

        $this->expectException(InternalServerErrorException::class);
        $this->expectExceptionMessage(GetUsersTest::INTERNAL_SERVER_ERROR_MESSAGE);

        $this->getUsersManager->getUsersAndStreamers();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
