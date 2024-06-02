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
    private const USERNAME = 'username';
    private const PASSWORD = 'password';
    private const ENDPOINT = '/analytics/users';
    private const INTERNAL_SERVER_ERROR_MESSAGE = 'Error del servidor al crear el usuario.';
    private const CONFLICT_ERROR_MESSAGE = 'El nombre de usuario ya está en uso.';
    private const CREATE_USER_MESSAGE = 'Usuario creado correctamente';
    private const BAD_REQUEST_ERROR_MESSAGE = 'Los parámetros (' . self::USERNAME . ' y ' . self::PASSWORD .
                                              ') no fueron proporcionados.';

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
    public function get_create_user_message()
    {
        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with(self::USERNAME)
            ->andReturn(false);
        $this->dbClient->shouldReceive('createUser')
            ->once()
            ->with(self::USERNAME, self::PASSWORD);

        $response = $this->postJson(self::ENDPOINT, [
            'username' => self::USERNAME,
            'password' => self::PASSWORD
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'username' => self::USERNAME,
            'message' => self::CREATE_USER_MESSAGE
        ]);
    }

    /**
     * @test
     */
    public function get_create_user_message_when_user_exists_returns_conflict_error()
    {
        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with(self::USERNAME)
            ->andReturn(true);

        $response = $this->postJson(self::ENDPOINT, [
            'username' => self::USERNAME,
            'password' => self::PASSWORD
        ]);

        $response->assertStatus(Response::HTTP_CONFLICT);
        $response->assertJson(['error' => self::CONFLICT_ERROR_MESSAGE]);
    }

    /**
     * @test
     */
    public function get_create_user_message_returns_internal_server_error()
    {
        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with(self::USERNAME)
            ->andReturn(false);
        $this->dbClient->shouldReceive('createUser')
            ->once()
            ->with(self::USERNAME, self::PASSWORD)
            ->andThrow(new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_MESSAGE));

        $response = $this->postJson(self::ENDPOINT, [
            'username' => self::USERNAME,
            'password' => self::PASSWORD
        ]);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJson(['error' => self::INTERNAL_SERVER_ERROR_MESSAGE]);
    }

    /**
     * @test
     */
    public function get_create_user_message_with_empty_username_returns_bad_request()
    {
        $response = $this->postJson(self::ENDPOINT, [
            'password' => self::PASSWORD
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['error' => self::BAD_REQUEST_ERROR_MESSAGE]);
    }

    /**
     * @test
     */
    public function get_create_user_message_with_empty_password_returns_bad_request()
    {
        $response = $this->postJson(self::ENDPOINT, [
            'username' => self::USERNAME
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['error' => self::BAD_REQUEST_ERROR_MESSAGE]);
    }

    /**
     * @test
     */
    public function get_create_user_message_with_empty_username_and_password_returns_bad_request()
    {
        $response = $this->postJson(self::ENDPOINT, []);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['error' => self::BAD_REQUEST_ERROR_MESSAGE]);
    }
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
