<?php

namespace Tests\Feature;

use App\Services\DBClient;
use App\Services\GetUsersManager;
use Tests\TestCase;
use Mockery;
use App\Exceptions\InternalServerErrorException;

class GetUsersTest extends TestCase
{
    private DBClient $databaseClient;
    private const GET_USERS_ERROR_MESSAGE = 'Error del servidor al obtener la lista de usuarios.';

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
    public function gets_users_and_streamers_successfully(): void
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

        $response = $this->get('/analytics/users');

        $response->assertStatus(200);
        $response->assertJson([
            ['username' => 'usuario1', 'followedStreamers' => $streamersFirstUser],
            ['username' => 'usuario2', 'followedStreamers' => $streamersSecondUser]
        ]);
    }

    /**
     * @test
     */
    public function gets_users_with_no_users_found(): void
    {
        $this->databaseClient
            ->expects('getUsers')
            ->andReturn([]);

        $response = $this->get('/analytics/users');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    /**
     * @test
     */
    public function gets_users_fails_due_to_database_error(): void
    {
        $this->databaseClient
            ->expects('getUsers')
            ->andThrow(new InternalServerErrorException(self::GET_USERS_ERROR_MESSAGE));

        $response = $this->get('/analytics/users');

        $response->assertStatus(500);
        $response->assertJson(['error' => self::GET_USERS_ERROR_MESSAGE]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
