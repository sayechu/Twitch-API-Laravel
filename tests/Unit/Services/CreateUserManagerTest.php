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
        $username = 'newuser';
        $password = 'password123';

        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with($username)
            ->andReturn(false);
        $this->dbClient->shouldReceive('createUser')
            ->once()
            ->with($username, $password)
            ->andReturn(true);

        $result = $this->createUserManager->getCreateUserMessage($username, $password);

        $this->assertEquals(['username' => $username, 'message' => 'Usuario creado correctamente'], $result);
    }

    /**
     * @test
     */
    public function throws_conflict_exception_if_username_exists()
    {
        $username = 'existingUser';
        $password = 'password123';

        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with($username)
            ->andReturn(true);

        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage("El nombre de usuario ya está en uso.");

        $this->createUserManager->getCreateUserMessage($username, $password);
    }

    /**
     * @test
     */
    public function throws_internal_server_error_exception_on_database_error()
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
            ->andThrow(new InternalServerErrorException("Database error during user creation"));

        $this->expectException(InternalServerErrorException::class);
        $this->expectExceptionMessage("Database error during user creation");

        $this->createUserManager->getCreateUserMessage($username, $password);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
