<?php

namespace Tests\Unit\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\InternalServerErrorException;
use App\Services\CreateUserManager;
use App\Services\DBClient;
use Mockery;
use Tests\TestCase;

class CreateUserManagerTest extends TestCase
{
    private DBClient $dbClient;
    private CreateUserManager $createUserManager;
    private const USERNAME = 'username';
    private const PASSWORD = 'password';
    private const INTERNAL_SERVER_ERROR_MESSAGE = 'Error del servidor al crear el usuario.';
    private const CONFLICT_ERROR_MESSAGE = 'El nombre de usuario ya está en uso.';
    private const CREATE_USER_MESSAGE = 'Usuario creado correctamente';

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbClient = Mockery::mock(DBClient::class);
        $this->createUserManager = new CreateUserManager($this->dbClient);
    }

    /**
     * @test
     */
    public function create_user_successfully()
    {
        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with(self::USERNAME)
            ->andReturn(false);
        $this->dbClient->shouldReceive('createUser')
            ->once()
            ->with(self::USERNAME, self::PASSWORD)
            ->andReturn(true);

        $result = $this->createUserManager->getCreateUserMessage(self::USERNAME, self::PASSWORD);

        $this->assertEquals(['username' => self::USERNAME, 'message' => self::CREATE_USER_MESSAGE], $result);
    }

    /**
     * @test
     */
    public function throws_conflict_exception_if_username_exists()
    {
        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with(self::USERNAME)
            ->andReturn(true);

        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage(self::CONFLICT_ERROR_MESSAGE);

        $this->createUserManager->getCreateUserMessage(self::USERNAME, self::PASSWORD);
    }

    /**
     * @test
     */
    public function throws_internal_server_error_exception_on_database_error()
    {
        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with(self::USERNAME)
            ->andReturn(false);
        $this->dbClient->shouldReceive('createUser')
            ->once()
            ->with(self::USERNAME, self::PASSWORD)
            ->andThrow(new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_MESSAGE));

        $this->expectException(InternalServerErrorException::class);
        $this->expectExceptionMessage(self::INTERNAL_SERVER_ERROR_MESSAGE);

        $this->createUserManager->getCreateUserMessage(self::USERNAME, self::PASSWORD);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
