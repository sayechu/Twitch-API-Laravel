<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Services\DBClient;
use App\Services\GetTimelineManager;
use Mockery;
use Illuminate\Support\Facades\Http;

class TimelineStreamsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockDBClient = Mockery::mock(DBClient::class);
        $this->app->instance(DBClient::class, $this->mockDBClient);
        $this->app->bind(GetTimelineManager::class, function ($app) {
            return new GetTimelineManager(
                $app->make('App\Services\TokenProvider'),
                $app->make('App\Services\TimelineStreamersProvider'),
                $app->make('App\Services\TimelineStreamsProvider')
            );
        });
    }

    /**
     * @test
     */
    public function returns_a_timeline_for_a_valid_user()
    {
        $user = User::factory()->create(['username' => 'validUser']);
        $this->mockDBClient->shouldReceive('isUserStored')->andReturn(true);
        $this->mockDBClient->shouldReceive('getStreamers')->andReturn(['streamer1', 'streamer2']);
        $this->mockDBClient->shouldReceive('getTimelineStreams')->andReturn([
            ['streamerName' => 'streamer1', 'streamDetails' => []],
            ['streamerName' => 'streamer2', 'streamDetails' => []]
        ]);

        Http::fake([
            'https://api.twitch.tv/helix/videos' => Http::response(['data' => []], 200)
        ]);

        $response = $this->getJson("/api/timeline?username={$user->username}");

        $response->assertOk();
        $response->assertJson([
            ['streamerName' => 'streamer1', 'streamDetails' => []],
            ['streamerName' => 'streamer2', 'streamDetails' => []]
        ]);
    }

    /**
     * @test
     */
    public function returns_not_found_for_an_invalid_user()
    {
        Http::fake();
        $username = 'nonExistentUser';
        $this->mockDBClient->shouldReceive('isUserStored')->with($username)->andReturn(false);

        $response = $this->getJson("/api/timeline?username={$username}");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertJson(['error' => "El usuario especificado ({$username}) no existe."]);
    }

    /**
     * @test
     */
    public function returns_internal_server_error_if_api_fails()
    {
        $user = User::factory()->create(['username' => 'apiFailUser']);
        $this->mockDBClient->shouldReceive('isUserStored')->andReturn(true);
        $this->mockDBClient->shouldReceive('getStreamers')->andReturn(['streamer1']);

        Http::fake([
            'https://api.twitch.tv/helix/videos*' => Http::response(null, 500)
        ]);

        $response = $this->getJson("/api/timeline?username={$user->username}");

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJson(['error' => 'No se puede establecer conexión con Twitch en este momento']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
