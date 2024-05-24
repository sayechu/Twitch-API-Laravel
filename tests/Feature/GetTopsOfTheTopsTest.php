<?php

namespace Feature;

use Illuminate\Foundation\Testing\TestCase;
use App\Services\TopVideosProvider;
use App\Services\TopGamesProvider;
use App\Services\TokenProvider;
use Illuminate\Http\Response;
use App\Services\ApiClient;
use App\Services\DBClient;
use Mockery;

class GetTopsOfTheTopsTest extends TestCase
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->databaseClient = Mockery::mock(DBClient::class);
        $this->app
            ->when(TokenProvider::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
        $this->app
            ->when(TokenProvider::class)
            ->needs(DBClient::class)
            ->give(fn() => $this->databaseClient);
        $this->app
            ->when(TopGamesProvider::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
        $this->app
            ->when(TopGamesProvider::class)
            ->needs(DBClient::class)
            ->give(fn() => $this->databaseClient);
        $this->app
            ->when(TopVideosProvider::class)
            ->needs(ApiClient::class)
            ->give(fn() => $this->apiClient);
        $this->app
            ->when(TopVideosProvider::class)
            ->needs(DBClient::class)
            ->give(fn() => $this->databaseClient);
    }

    /**
     * @test
     */
    public function tops_of_the_tops()
    {
        $this->assertEquals(1,1);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
