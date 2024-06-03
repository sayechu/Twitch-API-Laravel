<?php

namespace Tests\Unit\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\InternalServerErrorException;
use App\Services\CreateUserManager;
use App\Services\DBClient;
use Mockery;
use Tests\Builders\AnalyticsParameters;
use Tests\Feature\CreateUserTest;
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
        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with(AnalyticsParameters::USERNAME)
            ->andReturn(false);
        $this->dbClient->shouldReceive('createUser')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::PASSWORD)
            ->andReturn(true);

        $result = $this->createUserManager->getCreateUserMessage(AnalyticsParameters::USERNAME, AnalyticsParameters::PASSWORD);

        $this->assertEquals(['username' => AnalyticsParameters::USERNAME, 'message' => CreateUserTest::CREATE_USER_MESSAGE], $result);
    }

    /**
     * @test
     */
    public function throws_conflict_exception_if_username_exists()
    {
        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with(AnalyticsParameters::USERNAME)
            ->andReturn(true);

        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage(CreateUserTest::CONFLICT_ERROR_MESSAGE);

        $this->createUserManager->getCreateUserMessage(AnalyticsParameters::USERNAME, AnalyticsParameters::PASSWORD);
    }

    /**
     * @test
     */
    public function throws_internal_server_error_exception_on_database_error()
    {
        $this->dbClient->shouldReceive('checkIfUsernameExists')
            ->once()
            ->with(AnalyticsParameters::USERNAME)
            ->andReturn(false);
        $this->dbClient->shouldReceive('createUser')
            ->once()
            ->with(AnalyticsParameters::USERNAME, AnalyticsParameters::PASSWORD)
            ->andThrow(new InternalServerErrorException(CreateUserTest::INTERNAL_SERVER_ERROR_MESSAGE));

        $this->expectException(InternalServerErrorException::class);
        $this->expectExceptionMessage(CreateUserTest::INTERNAL_SERVER_ERROR_MESSAGE);

        $this->createUserManager->getCreateUserMessage(AnalyticsParameters::USERNAME, AnalyticsParameters::PASSWORD);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
