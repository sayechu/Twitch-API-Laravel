<?php

namespace Tests\Unit\Services;

use App\Services\TopsOfTheTopsManager;
use App\Services\TopVideosProvider;
use App\Services\TopGamesProvider;
use App\Services\TokenProvider;
use Illuminate\Http\Response;
use Tests\Builders\AnalyticsParameters;
use Tests\Feature\GetStreamsTest;
use Tests\TestCase;
use Exception;
use Mockery;

class TopsOfTheTopsManagerTest extends TestCase
{
    private const GET_TOP_GAMES_ERROR_MESSAGE = 'No se pueden devolver top games en este momento, inténtalo más tarde';
    private const GET_TOP_VIDEOS_ERROR_MESSAGE =
        'No se pueden devolver top 40 videos en este momento, inténtalo más tarde';
    private TopsOfTheTopsManager $topsOfTheTopsManager;
    private TokenProvider $tokenProvider;
    private TopGamesProvider $topGamesProvider;
    private TopVideosProvider $topVideosProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenProvider = Mockery::mock(TokenProvider::class);
        $this->topGamesProvider = Mockery::mock(TopGamesProvider::class);
        $this->topVideosProvider = Mockery::mock(TopVideosProvider::class);
        $this->topsOfTheTopsManager = new TopsOfTheTopsManager($this->tokenProvider, $this->topGamesProvider, $this->topVideosProvider);
    }

    /**
     * @test
     */
    public function token_provider_returns_500_error()
    {
        $getTokenResponse = [
            'response' => null,
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn($getTokenResponse);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(GetStreamsTest::GET_TOKEN_ERROR_MESSAGE);

        $this->topsOfTheTopsManager->getTopVideosOfTopGames(AnalyticsParameters::SINCE_TIME);
    }

    /**
     * @test
     */
    public function token_provider_returns_token_and_top_games_returns_500_error()
    {
        $topGamesResponse = [
            'response' => null,
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn(AnalyticsParameters::TWITCH_TOKEN);
        $this->topGamesProvider
            ->expects('getTopThreeGames')
            ->once()
            ->with([0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->andReturn($topGamesResponse);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(self::GET_TOP_GAMES_ERROR_MESSAGE);

        $this->topsOfTheTopsManager->getTopVideosOfTopGames(AnalyticsParameters::SINCE_TIME);
    }

    /**
     * @test
     */
    public function token_provider_and_top_games_provider_returns_response_but_top_videos_returns_500_error()
    {
        $topGamesResponse = [
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
                'id' => '21779',
                'name' => 'League of Legends',
                'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/21779-{width}x{height}.jpg',
                'igdb_id' => '115'
            ]
        ];
        $topVideosResponse = [
            'response' => null,
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn(AnalyticsParameters::TWITCH_TOKEN);
        $this->topGamesProvider
            ->expects('getTopThreeGames')
            ->once()
            ->with([0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->andReturn($topGamesResponse);
        $this->topVideosProvider
            ->expects('getTopFourtyVideos')
            ->once()
            ->andReturn($topVideosResponse);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(self::GET_TOP_VIDEOS_ERROR_MESSAGE);

        $this->topsOfTheTopsManager->getTopVideosOfTopGames(AnalyticsParameters::SINCE_TIME);
    }

    /**
     * @test
     */
    public function get_top_videos_of_top_games_returns_top_videos()
    {
        $topGamesResponse = [
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
                'id' => '21779',
                'name' => 'League of Legends',
                'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/21779-{width}x{height}.jpg',
                'igdb_id' => '115'
            ]
        ];
        $topVideosResponse = [
            [
                'game_id' => '509658',
                'game_name' => 'Just Chatting',
                'user_name' => 'elxokas',
                'total_videos' => '1',
                'total_views' => '3525329',
                'most_viewed_title' => 'TRAILER DE JESUCRISTO',
                'most_viewed_views' => '3525329',
                'most_viewed_duration' => '59s',
                'most_viewed_created_at' => '2020-05-15T17:27:17Z'
            ]
        ];
        $expectedResponse = [
            [
                [
                    'game_id' => '509658',
                    'game_name' => 'Just Chatting',
                    'user_name' => 'elxokas',
                    'total_videos' => '1',
                    'total_views' => '3525329',
                    'most_viewed_title' => 'TRAILER DE JESUCRISTO',
                    'most_viewed_views' => '3525329',
                    'most_viewed_duration' => '59s',
                    'most_viewed_created_at' => '2020-05-15T17:27:17Z'
                ]
            ],
            [
                [
                    'game_id' => '509658',
                    'game_name' => 'Just Chatting',
                    'user_name' => 'elxokas',
                    'total_videos' => '1',
                    'total_views' => '3525329',
                    'most_viewed_title' => 'TRAILER DE JESUCRISTO',
                    'most_viewed_views' => '3525329',
                    'most_viewed_duration' => '59s',
                    'most_viewed_created_at' => '2020-05-15T17:27:17Z'
                ]
            ],
            [
                [
                    'game_id' => '509658',
                    'game_name' => 'Just Chatting',
                    'user_name' => 'elxokas',
                    'total_videos' => '1',
                    'total_views' => '3525329',
                    'most_viewed_title' => 'TRAILER DE JESUCRISTO',
                    'most_viewed_views' => '3525329',
                    'most_viewed_duration' => '59s',
                    'most_viewed_created_at' => '2020-05-15T17:27:17Z'
                ]
            ]
        ];

        $this->tokenProvider
            ->expects('getToken')
            ->once()
            ->andReturn(AnalyticsParameters::TWITCH_TOKEN);
        $this->topGamesProvider
            ->expects('getTopThreeGames')
            ->once()
            ->with([0 => 'Authorization: Bearer ' . AnalyticsParameters::TWITCH_TOKEN])
            ->andReturn($topGamesResponse);
        $this->topVideosProvider
            ->shouldReceive('getTopFourtyVideos')
            ->andReturn($topVideosResponse);

        $topVideosOfTopGames = $this->topsOfTheTopsManager->getTopVideosOfTopGames(AnalyticsParameters::SINCE_TIME);

        $this->assertEquals($expectedResponse, $topVideosOfTopGames);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
