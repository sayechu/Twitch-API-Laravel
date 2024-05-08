<?php

namespace Tests\Feature;

use App\Services\ApiClient;
use App\Services\DBClient;
use App\Services\StreamsDataProvider;
use App\Services\TokenProvider;
use Tests\TestCase;
use Mockery;

class GetStreamsTest extends TestCase
{
    /**
     * @test
     */
    public function gets_streams_with_token_stored_in_database(): void
    {
        $apiClient = Mockery::mock(ApiClient::class);
        $databaseClient = Mockery::mock(DBClient::class);
        $this->app
            ->when(TokenProvider::class)
            ->needs(ApiClient::class)
            ->give(fn() => $apiClient);
        $this->app
            ->when(TokenProvider::class)
            ->needs(DBClient::class)
            ->give(fn() => $databaseClient);
        $this->app
            ->when(StreamsDataProvider::class)
            ->needs(ApiClient::class)
            ->give(fn() => $apiClient);

        $getStreamsExpectedResponse = [
            'response' => json_encode([
                'data' => [
                    [
                        'id' => '40627613557',
                        'user_id' => '92038375',
                        'user_login' => 'caedrel',
                        'user_name' => 'User Name',
                        'game_id' => '21779',
                        'game_name' => 'League of Legends',
                        'type' => 'live',
                        'title' => 'Stream Title',
                        'viewer_count' => 46181,
                        'started_at' => '2024-05-08T07:35:07Z',
                        'language' => 'en',
                        'thumbnail_url' => 'https://static-cdn.jtvnw.net/previews-ttv/live_user_caedrel-{width}x{height}.jpg',
                        'tag_ids' => [],
                        'tags' => ['xdd', 'Washed', 'degen', 'English', 'adhd', 'vtuber', 'Ratking', 'LPL', 'LCK', 'LEC'],
                        'is_mature' => false
                    ]
                ]
            ]),
            'http_code' => 200
        ];
        $databaseClient
            ->expects('isTokenStoredInDatabase')
            ->once()
            ->andReturn(true);
        $databaseClient
            ->expects('getToken')
            ->once()
            ->andReturn('zfmr6i7cbwken2maslfu9v89tvq9ne');
        $apiClient
            ->expects('makeCurlCall')
            ->with("https://api.twitch.tv/helix/streams", [0 => 'Authorization: Bearer zfmr6i7cbwken2maslfu9v89tvq9ne'])
            ->once()
            ->andReturn($getStreamsExpectedResponse);

        $response = $this->get('/analytics/streams');

        $response->assertStatus(200);
        $response->assertContent('[{"title":"Stream Title","user_name":"User Name"}]');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
