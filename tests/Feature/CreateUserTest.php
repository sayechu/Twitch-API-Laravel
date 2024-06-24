<?php

namespace Tests\Feature;

use Illuminate\Http\Response;
use App\Services\DBClient;
use App\Services\CreateUserManager;
use Tests\Builders\AnalyticsParameters;
use Tests\TestCase;
use Mockery;
use App\Exceptions\InternalServerErrorException;

class CreateUserTest extends TestCase
{
    private DBClient $dbClient;
    private CreateUserManager $createUserManager;
    public const INTERNAL_SERVER_ERROR_MESSAGE = 'Error del servidor al crear el usuario.';
    public const CONFLICT_ERROR_MESSAGE = 'El nombre de usuario ya está en uso.';
    public const CREATE_USER_MESSAGE = 'Usuario creado correctamente';

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
            ->with(AnalyticsParameters::USERNAME)
            ->andReturn(false);
        $this->dbClient->shouldReceive('createUser')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::PASSWORD);

        $response = $this->postJson(AnalyticsParameters::ANALYTICS_USERS_ENDPOINT, [
            'username' => AnalyticsParameters::USERNAME,
            'password' => AnalyticsParameters::PASSWORD
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'username' => AnalyticsParameters::USERNAME,
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
            ->with(AnalyticsParameters::USERNAME)
            ->andReturn(true);

        $response = $this->postJson(AnalyticsParameters::ANALYTICS_USERS_ENDPOINT, [
            'username' => AnalyticsParameters::USERNAME,
            'password' => AnalyticsParameters::PASSWORD
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
            ->with(AnalyticsParameters::USERNAME)
            ->andReturn(false);
        $this->dbClient->shouldReceive('createUser')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::PASSWORD)
            ->andThrow(new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_MESSAGE));

        $response = $this->postJson(AnalyticsParameters::ANALYTICS_USERS_ENDPOINT, [
            'username' => AnalyticsParameters::USERNAME,
            'password' => AnalyticsParameters::PASSWORD
        ]);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJson(['error' => self::INTERNAL_SERVER_ERROR_MESSAGE]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
