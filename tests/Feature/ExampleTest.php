<?php

namespace Tests\Feature;

use App\Services\ApiClient;
use App\Services\StreamsManager;
use Tests\TestCase;
use Mockery;

class ExampleTest extends TestCase
{
    /**
     * @test
     */
    public function gets_streams()
    {
        $apiClient = Mockery::mock(ApiClient::class);
        $this->app
            ->when(StreamsManager::class)
            ->needs(ApiClient::class)
            ->give(fn() => $apiClient);
        $getTokenExpectedResponse = json_encode([
            'access_token' => 'zfmr6i7cbwken2maslfu9v89tvq9ne',
            'expires_in' => 5443987,
            'token_type' => 'bearer'
        ]);
        $getStreamsExpectedResponse = json_encode(['data' => [[
            'title' => 'Stream title',
            'user_name' => 'user_name'
        ]]]);

        $apiClient
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenExpectedResponse);
        $twitchToken = json_decode($getTokenExpectedResponse, true)['access_token'];
        $apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/streams", [0 => 'Authorization: Bearer ' . $twitchToken])
            ->once()
            ->andReturn($getStreamsExpectedResponse);

        $response = $this->get('/analytics/streams');

        $response->assertStatus(200);
        $response->assertContent('[{"title":"Stream title","user_name":"user_name"}]');
    }
}
