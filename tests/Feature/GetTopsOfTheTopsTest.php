<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase;
use App\Services\TopVideosProvider;
use App\Services\TopGamesProvider;
use App\Services\TokenProvider;
use Illuminate\Http\Response;
use App\Services\ApiClient;
use App\Services\DBClient;
use Mockery;
use Tests\Builders\AnalyticsParameters;

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
        $topGamesResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => '509658',
                        'name' => 'Just Chatting'
                    ],
                    [
                        'id' => '32982',
                        'name' => 'Grand Theft Auto V'
                    ],
                    [
                        'id' => '32399',
                        'name' => 'Counter-Strike'
                    ]
                ]
            ],
            'http_code' => Response::HTTP_OK
        ];
        $videosResponse = [
            [
                'id' => 621881727,
                'user_id' => 31919607,
                'user_name' => 'elxokas',
                'view_count' => '3538395',
                'duration' => '59s',
                'created_at' => '2020-05-15T17:27:17Z',
                'title' => 'TRAILER DE JESUCRISTO',
                'game_id' => '509658',
                'game_name' => 'Just Chatting'
            ],
            [
                'id' => 621881727,
                'user_id' => 31919607,
                'user_name' => 'elxokas',
                'view_count' => '3538395',
                'duration' => '59s',
                'created_at' => '2020-05-15T17:27:17Z',
                'title' => 'TRAILER DE JESUCRISTO',
                'game_id' => '509658',
                'game_name' => 'Just Chatting'
            ],
        ];
        $expectedResponse = json_encode([
            [
                'game_id' => '509658',
                'game_name' => 'Just Chatting',
                'user_name' => 'elxokas',
                'total_videos' => '2',
                'total_views' => '7076790',
                'most_viewed_title' => 'TRAILER DE JESUCRISTO',
                'most_viewed_views' => '3538395',
                'most_viewed_duration' => '59s',
                'most_viewed_created_at' => '2020-05-15T17:27:17Z'
            ],
            [
                'game_id' => '509658',
                'game_name' => 'Just Chatting',
                'user_name' => 'elxokas',
                'total_videos' => '2',
                'total_views' => '7076790',
                'most_viewed_title' => 'TRAILER DE JESUCRISTO',
                'most_viewed_views' => '3538395',
                'most_viewed_duration' => '59s',
                'most_viewed_created_at' => '2020-05-15T17:27:17Z'
            ],
            [
                'game_id' => '509658',
                'game_name' => 'Just Chatting',
                'user_name' => 'elxokas',
                'total_videos' => '2',
                'total_views' => '7076790',
                'most_viewed_title' => 'TRAILER DE JESUCRISTO',
                'most_viewed_views' => '3538395',
                'most_viewed_duration' => '59s',
                'most_viewed_created_at' => '2020-05-15T17:27:17Z'
            ]
        ]);

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->andReturn(true);
        $this->databaseClient
            ->expects('getToken')
            ->andReturn(AnalyticsParameters::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(AnalyticsParameters::TOP_GAMES_URL, [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->andReturn($topGamesResponse);
        $this->databaseClient
            ->shouldReceive('isGameStored')
            ->andReturn(false);
        $this->databaseClient
            ->shouldReceive('storeTopGame');
        $this->databaseClient
            ->shouldReceive('isDataStoredRecentlyFromGame')
            ->andReturn(true);
        $this->databaseClient
            ->shouldReceive('getVideosOfAGivenGame')
            ->andReturn($videosResponse);

        $topsOfTheTops = $this->get('/analytics/topsofthetops');

        $topsOfTheTops->assertStatus(200);
        $this->assertEquals($expectedResponse, $topsOfTheTops->getContent());
    }

    /**
     * @test
     */
    public function tops_of_the_tops_with_token_from_api()
    {
        $tokenResponse = [
            "response" => json_encode([
                'access_token' => AnalyticsParameters::TWITCH_TOKEN,
                'expires_in' => 5590782,
                'token_type' => 'bearer'
            ]),
            "http_code" => Response::HTTP_OK
        ];
        $topGamesResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => '509658',
                        'name' => 'Just Chatting'
                    ],
                    [
                        'id' => '32982',
                        'name' => 'Grand Theft Auto V'
                    ],
                    [
                        'id' => '32399',
                        'name' => 'Counter-Strike'
                    ]
                ]
            ],
            'http_code' => Response::HTTP_OK
        ];
        $videosResponse = [
            [
                'id' => 621881727,
                'user_id' => 31919607,
                'user_name' => 'elxokas',
                'view_count' => '3538395',
                'duration' => '59s',
                'created_at' => '2020-05-15T17:27:17Z',
                'title' => 'TRAILER DE JESUCRISTO',
                'game_id' => '509658',
                'game_name' => 'Just Chatting'
            ],
            [
                'id' => 621881727,
                'user_id' => 31919607,
                'user_name' => 'elxokas',
                'view_count' => '3538395',
                'duration' => '59s',
                'created_at' => '2020-05-15T17:27:17Z',
                'title' => 'TRAILER DE JESUCRISTO',
                'game_id' => '509658',
                'game_name' => 'Just Chatting'
            ],
        ];
        $expectedResponse = json_encode([
            [
                'game_id' => '509658',
                'game_name' => 'Just Chatting',
                'user_name' => 'elxokas',
                'total_videos' => '2',
                'total_views' => '7076790',
                'most_viewed_title' => 'TRAILER DE JESUCRISTO',
                'most_viewed_views' => '3538395',
                'most_viewed_duration' => '59s',
                'most_viewed_created_at' => '2020-05-15T17:27:17Z'
            ],
            [
                'game_id' => '509658',
                'game_name' => 'Just Chatting',
                'user_name' => 'elxokas',
                'total_videos' => '2',
                'total_views' => '7076790',
                'most_viewed_title' => 'TRAILER DE JESUCRISTO',
                'most_viewed_views' => '3538395',
                'most_viewed_duration' => '59s',
                'most_viewed_created_at' => '2020-05-15T17:27:17Z'
            ],
            [
                'game_id' => '509658',
                'game_name' => 'Just Chatting',
                'user_name' => 'elxokas',
                'total_videos' => '2',
                'total_views' => '7076790',
                'most_viewed_title' => 'TRAILER DE JESUCRISTO',
                'most_viewed_views' => '3538395',
                'most_viewed_duration' => '59s',
                'most_viewed_created_at' => '2020-05-15T17:27:17Z'
            ]
        ]);

        $this->databaseClient
            ->expects('isTokenStoredInDatabase')
            ->andReturn(false);
        $this->apiClient
            ->expects('getToken')
            ->andReturn($tokenResponse);
        $this->databaseClient
            ->expects('storeToken')
            ->with(AnalyticsParameters::TWITCH_TOKEN);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with(AnalyticsParameters::TOP_GAMES_URL, [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->andReturn($topGamesResponse);
        $this->databaseClient
            ->shouldReceive('isGameStored')
            ->andReturn(false);
        $this->databaseClient
            ->shouldReceive('storeTopGame');
        $this->databaseClient
            ->shouldReceive('isDataStoredRecentlyFromGame')
            ->andReturn(true);
        $this->databaseClient
            ->shouldReceive('getVideosOfAGivenGame')
            ->andReturn($videosResponse);

        $topsOfTheTops = $this->get('/analytics/topsofthetops');

        $this->assertEquals($expectedResponse, $topsOfTheTops->getContent());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
