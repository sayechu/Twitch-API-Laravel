<?php

namespace Tests\Feature;

use Illuminate\Http\Response;
use App\Services\DBClient;
use App\Services\GetUsersManager;
use Tests\TestCase;
use Mockery;
use App\Exceptions\InternalServerErrorException;

class GetUsersTest extends TestCase
{
    private DBClient $dbClient;
    private GetUsersManager $getUsersManager;
    private const GET_USERS_ERROR_MESSAGE = 'Error del servidor al obtener la lista de usuarios.';
    protected function setUp(): void
    {
        parent::setUp();
        $this->dbClient = Mockery::mock(DBClient::class);
        $this->getUsersManager = new GetUsersManager($this->dbClient);
        $this->app->instance(GetUsersManager::class, $this->getUsersManager);
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
        $streamersUsuario1 = ['streamer1', 'streamer2'];
        $streamersUsuario2 = ['streamer2', 'streamer3'];

        $this->dbClient->expects('getUsers')
            ->once()
            ->andReturn($users);
        $this->dbClient->expects('getStreamers')
            ->with('usuario1')
            ->once()
            ->andReturn($streamersUsuario1);
        $this->dbClient->expects('getStreamers')
            ->with('usuario2')
            ->once()
            ->andReturn($streamersUsuario2);

        $response = $this->get('/analytics/users');

        $response->assertStatus(200);
        $response->assertJson([
            ['username' => 'usuario1', 'followedStreamers' => $streamersUsuario1],
            ['username' => 'usuario2', 'followedStreamers' => $streamersUsuario2]
        ]);
    }
    /**
     * @test
     */
    public function gets_users_with_no_users_found(): void
    {
        $this->dbClient->expects('getUsers')
            ->once()
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
        $this->dbClient->expects('getUsers')
            ->once()
            ->andThrow(new InternalServerErrorException('Error del servidor al obtener la lista de usuarios.'));

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
