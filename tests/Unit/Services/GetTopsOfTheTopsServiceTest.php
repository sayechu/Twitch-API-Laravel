<?php

namespace Tests\Unit\Services;

use App\Services\GetTopsOfTheTopsService;
use App\Services\TopsOfTheTopsManager;
use Tests\Builders\TopVideosResponseBuilder;
use Tests\TestCase;
use Mockery;

class GetTopsOfTheTopsServiceTest extends TestCase
{
    private GetTopsOfTheTopsService $getTopsService;
    private TopsOfTheTopsManager $topsOfTheTopsManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->topsOfTheTopsManager = Mockery::mock(TopsOfTheTopsManager::class);
        $this->getTopsService = new GetTopsOfTheTopsService($this->topsOfTheTopsManager);
    }

    /**
     * @test
     */
    public function execute_returns_tops_of_the_tops()
    {
        $responseBuilder = (new TopVideosResponseBuilder())->withTestValues();
        $responseVideos = $responseBuilder->build();
        $expectedResponse = $responseBuilder->buildExpected();

        $this->topsOfTheTopsManager
            ->expects('getTopVideosOfTopGames')
            ->once()
            ->andReturn($responseVideos);

        $topsOfTheTops = $this->getTopsService->execute(600);

        $this->assertEquals($expectedResponse, $topsOfTheTops);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
