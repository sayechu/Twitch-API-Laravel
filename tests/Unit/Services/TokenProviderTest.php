<?php

namespace Tests\Unit\Services;

use App\Services\ApiClient;
use App\Services\DBClient;
use App\Services\TokenProvider;
use Mockery;
use Tests\TestCase;

class TokenProviderTest extends TestCase
{
    private DBClient $databaseClient;
    private ApiClient $apiClient;
    private TokenProvider $tokenProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseClient = Mockery::mock(DBClient::class);
        $this->apiClient = Mockery::mock(ApiClient::class);

        $this->tokenProvider = new TokenProvider($this->apiClient, $this->databaseClient);
    }

    /**
     * @test
     */
    public function get_token_from_database(): void
    {
        $tokenExpectedResponse = 'nrtovbe5h02os45krmjzvkt3hp74vf';

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(true);

        $this->databaseClient
            ->expects('getToken')
            ->once()
            ->andReturn($tokenExpectedResponse);

        $tokenResponse = $this->tokenProvider->getToken();

        $this->assertEquals('nrtovbe5h02os45krmjzvkt3hp74vf', $tokenResponse);
    }

    /**
     * @test
     */
    public function get_token_api_failure(): void
    {
        $tokenApiExpectedResponse = [
            "response" => null,
            "http_code" => 500
        ];

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(false);

        $this->apiClient
            ->expects('getToken')
            ->once()
            ->andReturn($tokenApiExpectedResponse);

        $tokenResponse = $this->tokenProvider->getToken();

        $this->assertEquals(500, $tokenResponse['http_code']);
    }

    /**
     * @test
     */
    public function get_token_test(): void
    {
        $tokenApiExpectedResponse = [
            "response" => '{"access_token":"uos0bg0st4mexopq3rhs361mny1fmt","expires_in":5590782,"token_type":"bearer"}',
            "http_code" => 200
        ];

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(false);

        $this->apiClient
            ->expects('getToken')
            ->once()
            ->andReturn($tokenApiExpectedResponse);

        $this->databaseClient
            ->shouldReceive('addToken')
            ->once()
            ->with('uos0bg0st4mexopq3rhs361mny1fmt');

        $tokenResponse = $this->tokenProvider->getToken();

        $this->assertEquals('uos0bg0st4mexopq3rhs361mny1fmt', $tokenResponse);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
