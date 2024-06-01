<?php

namespace Tests\Unit\Services;

use App\Exceptions\InternalServerErrorException;
use App\Exceptions\NotFoundException;
use App\Services\UnfollowManager;
use App\Services\DBClient;
use Tests\TestCase;
use Mockery;

class UnfollowManagerTest extends TestCase
{
    private UnfollowManager $unfollowManager;
    private DBClient $databaseClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseClient = Mockery::mock(DBClient::class);
        $this->unfollowManager = new UnfollowManager($this->databaseClient);
    }

    /**
     * @test
     */
    public function unfollow_streamer(): void
    {
        $username = 'username';
        $streamerId = 1234;
        $expectedResponse = [
            "message" => "Dejaste de seguir a {$streamerId}"
        ];

        $this->databaseClient
            ->expects('isUserFollowingStreamer')
            ->once()
            ->with($username, $streamerId)
            ->andReturn(true);
        $this->databaseClient
            ->expects('unfollowStreamer')
            ->once()
            ->with($username, $streamerId);

        $actualResponse = $this->unfollowManager->unfollowStreamer($username, $streamerId);

        $this->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * @test
     */
    public function unfollow_streamer_not_stored_returns_not_found_exception(): void
    {
        $username = 'username';
        $streamerId = 1234;
        $expectedResponse = "El usuario ({$username}) o el streamer ({$streamerId}) especificado no existe en la API.";

        $this->databaseClient
            ->expects('isUserFollowingStreamer')
            ->once()
            ->with($username, $streamerId)
            ->andReturn(false);
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage($expectedResponse);

        $this->unfollowManager->unfollowStreamer($username, $streamerId);
    }

    /**
     * @test
     */
    public function unfollow_streamer_returns_internal_server_error_exception_on_db_failure(): void
    {
        $username = 'username';
        $streamerId = 1234;
        $expectedResponse = 'Error del servidor al dejar de seguir al streamer.';

        $this->databaseClient
            ->expects('isUserFollowingStreamer')
            ->once()
            ->with($username, $streamerId)
            ->andThrows(InternalServerErrorException::class, $expectedResponse);
        $this->expectException(InternalServerErrorException::class);
        $this->expectExceptionMessage($expectedResponse);

        $this->unfollowManager->unfollowStreamer($username, $streamerId);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
