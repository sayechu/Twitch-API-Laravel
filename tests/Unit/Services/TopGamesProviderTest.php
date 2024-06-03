<?php

namespace Tests\Unit\Services;

use App\Services\TopGamesProvider;
use Illuminate\Http\Response;
use App\Services\ApiClient;
use App\Services\DBClient;
use Mockery;
use Tests\Builders\AnalyticsParameters;
use Tests\TestCase;

class TopGamesProviderTest extends TestCase
{
    private TopGamesProvider $topGamesProvider;
    private DBClient $databaseClient;
    private ApiClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseClient = Mockery::mock(DBClient::class);
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->topGamesProvider = new TopGamesProvider($this->apiClient, $this->databaseClient);
    }

    /**
     * @test
     */
    public function get_top_three_games_returns_500_code(): void
    {
        $topGamesResponse = [
            'response' => null,
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

        $this->apiClient
            ->expects('makeCurlCall')
            ->with('https://api.twitch.tv/helix/games/top?first=3', [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->andReturn($topGamesResponse);

        $getTopGamesResponse = $this->topGamesProvider->getTopThreeGames([0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN]);

        $this->assertEquals($topGamesResponse, $getTopGamesResponse);
    }

    /**
     * @test
     */
    public function get_top_3_games_returns_top_games_stored_in_db()
    {
        $topGamesResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => '509658',
                        'name' => 'Just Chatting',
                        'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/509658-{width}x{height}.jpg',
                        'igdb_id' => ''
                    ],
                    [
                        'id' => '32982',
                        'name' => 'Grand Theft Auto V',
                        'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/32982_IGDB-{width}x{height}.jpg',
                        'igdb_id' => '1020'
                    ],
                    [
                        'id' => '33214',
                        'name' => 'Fortnite',
                        'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/33214-{width}x{height}.jpg',
                        'igdb_id' => '1905'
                    ]
                ]],
            'http_code' => Response::HTTP_OK
        ];
        $expectedResponse = [
            [
                'id' => '509658',
                'name' => 'Just Chatting',
                'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/509658-{width}x{height}.jpg',
                'igdb_id' => ''
            ],
            [
                'id' => '32982',
                'name' => 'Grand Theft Auto V',
                'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/32982_IGDB-{width}x{height}.jpg',
                'igdb_id' => '1020'
            ],
            [
                'id' => '33214',
                'name' => 'Fortnite',
                'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/33214-{width}x{height}.jpg',
                'igdb_id' => '1905'
            ]
        ];

        $this->apiClient
            ->expects('makeCurlCall')
            ->with('https://api.twitch.tv/helix/games/top?first=3', [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->andReturn($topGamesResponse);
        $this->databaseClient
            ->shouldReceive('isGameStored')
            ->andReturn(true);

        $getTopGamesResponse = $this->topGamesProvider->getTopThreeGames([0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN]);

        $this->assertEquals($expectedResponse, $getTopGamesResponse);
    }

    /**
     * @test
     */
    public function get_top_3_games_returns_top_games_not_stored_in_db()
    {
        $topGamesResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => '509658',
                        'name' => 'Just Chatting',
                        'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/509658-{width}x{height}.jpg',
                        'igdb_id' => ''
                    ],
                    [
                        'id' => '32982',
                        'name' => 'Grand Theft Auto V',
                        'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/32982_IGDB-{width}x{height}.jpg',
                        'igdb_id' => '1020'
                    ],
                    [
                        'id' => '33214',
                        'name' => 'Fortnite',
                        'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/33214-{width}x{height}.jpg',
                        'igdb_id' => '1905'
                    ]
                ]],
            'http_code' => Response::HTTP_OK
        ];
        $expectedResponse = [
            [
                'id' => '509658',
                'name' => 'Just Chatting',
                'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/509658-{width}x{height}.jpg',
                'igdb_id' => ''
            ],
            [
                'id' => '32982',
                'name' => 'Grand Theft Auto V',
                'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/32982_IGDB-{width}x{height}.jpg',
                'igdb_id' => '1020'
            ],
            [
                'id' => '33214',
                'name' => 'Fortnite',
                'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/33214-{width}x{height}.jpg',
                'igdb_id' => '1905'
            ]
        ];

        $this->apiClient
            ->expects('makeCurlCall')
            ->with('https://api.twitch.tv/helix/games/top?first=3', [0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->andReturn($topGamesResponse);
        $this->databaseClient
            ->shouldReceive('isGameStored')
            ->andReturn(false);
        $this->databaseClient
            ->shouldReceive('storeTopGame');

        $getTopGamesResponse = $this->topGamesProvider->getTopThreeGames([0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN]);

        $this->assertEquals($expectedResponse, $getTopGamesResponse);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
