<?php

namespace Tests\Feature;

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
            ->with('https://id.twitch.tv/oauth2/token')
            ->once()
            ->andReturn($getTokenExpectedResponse);
        $apiClient
            ->expects('makeCurlCall')
            ->with('https://id.twitch.tv/helix/streams', [0 => 'Authorization: Bearer zfmr6i7cbwken2maslfu9v89tvq9ne'])
            ->once()
            ->andReturn($getStreamsExpectedResponse);

        $response = $this->get('/analytics/streams');

        $response->assertStatus(200);
        $response->assertContent('[{"title":"Stream title", "user_name":"user_name"}]');
    }

}
