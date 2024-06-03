<?php

namespace Tests\Feature;

use App\Exceptions\InternalServerErrorException;
use Illuminate\Foundation\Testing\TestCase;
use App\Services\UnfollowManager;
use Illuminate\Http\Response;
use App\Services\DBClient;
use Mockery;

class UnfollowTest extends TestCase
{
    private DBClient $databaseClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseClient = Mockery::mock(DBClient::class);
        $this->app
            ->when(UnfollowManager::class)
            ->needs(DBClient::class)
            ->give(fn() => $this->databaseClient);
    }

    /**
     * @test
     */
    public function unfollow_streamer(): void
    {
        $username = 'username';
        $streamerId = 1234;
        $headers = ['Content-Type' => 'application/json'];
        $body = [
            'username' => $username,
            'streamerId' => $streamerId
        ];
        $expectedResponse = [
            "message" => "Dejaste de seguir a {$streamerId}"
        ];

        $this->databaseClient
            ->expects('isUserFollowingStreamer')
            ->with($username, $streamerId)
            ->andReturn(true);
        $this->databaseClient
            ->expects('unfollowStreamer')
            ->with($username, $streamerId);

        $unfollowResponse = $this->json('DELETE', '/analytics/unfollow', $body, $headers);

        $unfollowResponse->assertStatus(Response::HTTP_OK);
        $unfollowResponse->assertExactJson($expectedResponse);
    }

    /**
     * @test
     */
    public function unfollow_streamer_returns_not_found_exception(): void
    {
        $username = 'username';
        $streamerId = 1234;
        $headers = ['Content-Type' => 'application/json'];
        $body = [
            'username' => $username,
            'streamerId' => $streamerId
        ];
        $expectedResponse = ["El usuario ({$username}) o el streamer ({$streamerId}) especificado no existe en la API."];

        $this->databaseClient
            ->expects('isUserFollowingStreamer')
            ->with($username, $streamerId)
            ->andReturn(false);

        $unfollowResponse = $this->json('DELETE', '/analytics/unfollow', $body, $headers);

        $unfollowResponse->assertStatus(Response::HTTP_NOT_FOUND);
        $unfollowResponse->assertExactJson($expectedResponse);
    }

    /**
     * @test
     */
    public function unfollow_streamer_returns_internal_server_error_exception(): void
    {
        $username = 'username';
        $streamerId = 1234;
        $headers = ['Content-Type' => 'application/json'];
        $body = [
            'username' => $username,
            'streamerId' => $streamerId
        ];
        $expectedResponse = 'Error del servidor al dejar de seguir al streamer.';

        $this->databaseClient
            ->expects('isUserFollowingStreamer')
            ->with($username, $streamerId)
            ->andThrows(InternalServerErrorException::class, $expectedResponse);

        $unfollowResponse = $this->json('DELETE', '/analytics/unfollow', $body, $headers);

        $unfollowResponse->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $unfollowResponse->assertExactJson([$expectedResponse]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
