<?php

namespace Tests\Feature;

use App\Services\DBClient;
use App\Services\GetUsersManager;
use Tests\Builders\AnalyticsParameters;
use Tests\TestCase;
use Mockery;
use App\Exceptions\InternalServerErrorException;
use Illuminate\Http\Response;

class GetUsersTest extends TestCase
{
    private DBClient $databaseClient;
    public const INTERNAL_SERVER_ERROR_MESSAGE = 'Error del servidor al obtener la lista de usuarios.';

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseClient = Mockery::mock(DBClient::class);
        $this->app
            ->when(GetUsersManager::class)
            ->needs(DBClient::class)
            ->give(fn() => $this->databaseClient);
    }

    /**
     * @test
     */
    public function gets_users_and_streamers(): void
    {
        $users = [
            ['username' => 'usuario1'],
            ['username' => 'usuario2']
        ];
        $streamersFirstUser = ['streamer1', 'streamer2'];
        $streamersSecondUser = ['streamer2', 'streamer3'];

        $this->databaseClient
            ->expects('getUsers')
            ->andReturn($users);
        $this->databaseClient
            ->expects('getStreamers')
            ->with('usuario1')
            ->andReturn($streamersFirstUser);
        $this->databaseClient
            ->expects('getStreamers')
            ->with('usuario2')
            ->andReturn($streamersSecondUser);

        $response = $this->get(AnalyticsParameters::ANALYTICS_USERS_ENDPOINT);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            ['username' => 'usuario1', 'followedStreamers' => $streamersFirstUser],
            ['username' => 'usuario2', 'followedStreamers' => $streamersSecondUser]
        ]);
    }

    /**
     * @test
     */
    public function gets_users_returns_database_error(): void
    {
        $this->databaseClient
            ->expects('getUsers')
            ->andThrow(new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_MESSAGE));

        $response = $this->get(AnalyticsParameters::ANALYTICS_USERS_ENDPOINT);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJson(['error' => self::INTERNAL_SERVER_ERROR_MESSAGE]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
