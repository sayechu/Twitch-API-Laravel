<?php

namespace Tests\Feature;

use Illuminate\Http\Response;
use App\Services\DBClient;
use App\Services\CreateUserManager;
use Tests\TestCase;
use Mockery;
use App\Exceptions\ConflictException;
use App\Exceptions\InternalServerErrorException;

class CreateUserTest extends TestCase
{
    private DBClient $dbClient;
    private CreateUserManager $createUserManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbClient = Mockery::mock(DBClient::class);
        $this->createUserManager = new CreateUserManager($this->dbClient);
        $this->app->instance(CreateUserManager::class, $this->createUserManager);
    }

    /**
     * @test
     */
    public function create_user_successfully()
    {
        $username = 'newuser';
        $password = 'password123';

        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with($username)
            ->andReturn(false);
        $this->dbClient->shouldReceive('createUser')
            ->once()
            ->with($username, $password);

        $response = $this->postJson('/analytics/users', [
            'username' => $username,
            'password' => $password
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'username' => $username,
            'message' => 'Usuario creado correctamente'
        ]);
    }

    /**
     * @test
     */
    public function conflict_exception_if_username_exists()
    {
        $username = 'existingUser';
        $password = 'password123';

        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with($username)
            ->andReturn(true);

        $response = $this->postJson('/analytics/users', [
            'username' => $username,
            'password' => $password
        ]);

        $response->assertStatus(409);
        $response->assertJson(['error' => 'El nombre de usuario ya está en uso.']);
    }

    /**
     * @test
     */
    public function internal_server_error_exception_if_database_error()
    {
        $username = 'newuser';
        $password = 'password123';

        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with($username)
            ->andReturn(false);
        $this->dbClient->shouldReceive('createUser')
            ->once()
            ->with($username, $password)
            ->andThrow(new InternalServerErrorException('Error del servidor al crear el usuario.'));

        $response = $this->postJson('/analytics/users', [
            'username' => $username,
            'password' => $password
        ]);

        $response->assertStatus(500);
        $response->assertJson(['error' => 'Error del servidor al crear el usuario.']);
    }
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
