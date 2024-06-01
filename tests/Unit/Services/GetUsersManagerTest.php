<?php

namespace Tests\Unit\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\InternalServerErrorException;
use App\Services\DBClient;
use App\Services\GetUsersManager;
use Mockery;
use Tests\TestCase;

class GetUsersManagerTest extends TestCase
{
    private DBClient $dbClient;
    private GetUsersManager $getUsersManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbClient = Mockery::mock(DBClient::class);
        $this->getUsersManager = new GetUsersManager($this->dbClient);
    }

    /**
     * @test
     */
    public function getUsersAndStreamers_returns_users_and_streamers(): void
    {
        $users = [
            ['username' => 'user1'],
            ['username' => 'user2']
        ];
        $streamersUser1 = ['streamer1', 'streamer2'];
        $streamersUser2 = ['streamer3', 'streamer4'];

        $this->dbClient->shouldReceive('getUsers')
            ->once()
            ->andReturn($users);
        $this->dbClient->shouldReceive('getStreamers')
            ->with('user1')
            ->once()
            ->andReturn($streamersUser1);
        $this->dbClient->shouldReceive('getStreamers')
            ->with('user2')
            ->once()
            ->andReturn($streamersUser2);

        $expectedResult = [
            ['username' => 'user1', 'followedStreamers' => $streamersUser1],
            ['username' => 'user2', 'followedStreamers' => $streamersUser2]
        ];

        $actualResult = $this->getUsersManager->getUsersAndStreamers();

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function getUsersAndStreamers_throws_InternalServerErrorException_on_db_failure(): void
    {
        $this->dbClient->shouldReceive('getUsers')
            ->once()
            ->andThrow(new InternalServerErrorException("Database query failed"));

        $this->expectException(InternalServerErrorException::class);
        $this->expectExceptionMessage("Database query failed");

        $this->getUsersManager->getUsersAndStreamers();
    }

    /**
     * @test
     */
    public function getUsersAndStreamers_handles_empty_user_list(): void
    {
        $this->dbClient->shouldReceive('getUsers')
            ->once()
            ->andReturn([]);

        $expectedResult = [];

        $actualResult = $this->getUsersManager->getUsersAndStreamers();

        $this->assertEquals($expectedResult, $actualResult);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
