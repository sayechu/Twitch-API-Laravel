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
    const TOKEN = "nrtovbe5h02os45krmjzvkt3hp74vf";

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
    public function get_token_stored(): void
    {
        $tokenResponse = 'nrtovbe5h02os45krmjzvkt3hp74vf';

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(true);
        $this->databaseClient
            ->expects('getToken')
            ->once()
            ->andReturn($tokenResponse);

        $getTokenResponse = $this->tokenProvider->getToken();

        $this->assertEquals($tokenResponse, $getTokenResponse);
    }

    /**
     * @test
     */
    public function get_token_returns_curl_error(): void
    {
        $apiResponse = [
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
            ->andReturn($apiResponse);

        $tokenResponse = $this->tokenProvider->getToken();

        $this->assertEquals(500, $tokenResponse['http_code']);
    }

    /**
     * @test
     */
    public function get_token_returns_token_response(): void
    {
        $apiResponse = [
            "response" => '{"access_token":"' . self::TOKEN . '","expires_in":5590782,"token_type":"bearer"}',
            "http_code" => 200
        ];

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(false);
        $this->apiClient
            ->expects('getToken')
            ->once()
            ->andReturn($apiResponse);
        $this->databaseClient
            ->expects('storeToken')
            ->once()
            ->with(self::TOKEN);

        $getTokenResponse = $this->tokenProvider->getToken();

        $this->assertEquals(self::TOKEN, $getTokenResponse);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
