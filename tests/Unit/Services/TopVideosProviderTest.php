<?php

namespace Tests\Unit\Services;

use App\Services\TopVideosProvider;
use Illuminate\Http\Response;
use App\Services\ApiClient;
use App\Services\DBClient;
use Tests\Builders\AnalyticsParameters;
use Tests\TestCase;
use Mockery;

class TopVideosProviderTest extends TestCase
{
    private const API_HEADERS = [0 => 'Bearer ' . AnalyticsParameters::TWITCH_TOKEN];
    private TopVideosProvider $topVideosProvider;
    private DBClient $databaseClient;
    private ApiClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseClient = Mockery::mock(DBClient::class);
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->topVideosProvider = new TopVideosProvider($this->apiClient, $this->databaseClient);
    }

    /**
     * @test
     */
    public function get_top_fourty_videos_returns_videos_from_database(): void
    {
        $getVideosResponse = [
            [
                'id' => 621881727,
                'user_id' => 31919607,
                'user_name' => 'elxokas',
                'view_count' => '3526311',
                'duration' => '59s',
                'created_at' => '2020-05-15T17:27:17Z',
                'title' => 'TRAILER DE JESUCRISTO',
                'game_id'=> '509658',
                'game_name' => 'Just Chatting'
            ]
        ];

        $this->databaseClient
            ->expects('isDataStoredRecentlyFromGame')
            ->with(AnalyticsParameters::GAME_ID, AnalyticsParameters::SINCE_TIME)
            ->andReturn(true);
        $this->databaseClient
            ->expects('getVideosOfAGivenGame')
            ->with(AnalyticsParameters::GAME_ID)
            ->andReturn($getVideosResponse);

        $topVideosResponse = $this->topVideosProvider->getTopFourtyVideos(
            AnalyticsParameters::GAME_ID,
            AnalyticsParameters::GAME_NAME,
            AnalyticsParameters::SINCE_TIME,
            self::API_HEADERS);

        $this->assertEquals($getVideosResponse, $topVideosResponse);
    }

    /**
     * @test
     */
    public function get_top_fourty_videos_from_api_returns_500_error()
    {
        $topVideosResponse = [
            'response' => null,
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

        $this->databaseClient
            ->expects('isDataStoredRecentlyFromGame')
            ->with(AnalyticsParameters::GAME_ID, AnalyticsParameters::SINCE_TIME)
            ->andReturn(false);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with('https://api.twitch.tv/helix/videos?game_id=' . AnalyticsParameters::GAME_ID . '&sort=views&first=40', self::API_HEADERS)
            ->andReturn($topVideosResponse);

        $getTopFourtyResponse = $this->topVideosProvider->getTopFourtyVideos(
            AnalyticsParameters::GAME_ID,
            AnalyticsParameters::GAME_NAME,
            AnalyticsParameters::SINCE_TIME,
            self::API_HEADERS);

        $this->assertEquals($topVideosResponse, $getTopFourtyResponse);
    }

    /**
     * @test
     */
    public function get_top_fourty_videos_from_api()
    {
        $topVideosResponse = [
            'response' => [
                'data' => [
                    [
                        'id' => '2147366426',
                        'stream_id' => '44203530427',
                        'user_id' => '641972806',
                        'user_login' => 'kaicenat',
                        'user_name' => 'KaiCenat',
                        'title' => '⚔️100+ HR STREAM⚔️ELDEN RING⚔️CLICK HERE⚔️GAMER⚔️BIGGEST DWARF⚔️ELITE⚔️PRAY 4 ME⚔️',
                        'description' => '',
                        'created_at' => '2024-05-16T18:54:23Z',
                        'published_at' => '2024-05-16T18:54:23Z',
                        'url' => 'https://www.twitch.tv/videos/2147366426',
                        'thumbnail_url' => 'https://static-cdn.jtvnw.net/cf_vods/d2nvs31859zcd8/96a8c66690b34cdc5122_kaicenat_44203530427_1715885658//thumb/thumb0-%{width}x%{height}.jpg',
                        'viewable' => 'public',
                        'view_count' => 12391801,
                        'language' => 'en',
                        'type' => 'archive',
                        'duration' => '26h33m41s',
                        'muted_segments' => null
                    ]
                ],
                'pagination' => [
                    'cursor' => 'eyJiIjpudWxsLCJhIjp7Ik9mZnNldCI6Mzh9fQ'
                ]
            ],
            'http_code' => Response::HTTP_OK
        ];
        $gameVideosResponse = [
            [
                'id' => 2147366426,
                'user_id' => 641972806,
                'user_name' => 'KaiCenat',
                'view_count' => 12391801,
                'duration' => '26h33m41s',
                'created_at' => '2024-05-16T18:54:23Z',
                'title' => '⚔️100+ HR STREAM⚔️ELDEN RING⚔️CLICK HERE⚔️GAMER⚔️BIGGEST DWARF⚔️ELITE⚔️PRAY 4 ME⚔️',
                'game_id' => AnalyticsParameters::GAME_ID,
                'game_name' => AnalyticsParameters::GAME_NAME
            ]
        ];

        $this->databaseClient
            ->expects('isDataStoredRecentlyFromGame')
            ->with(AnalyticsParameters::GAME_ID, AnalyticsParameters::SINCE_TIME)
            ->andReturn(false);
        $this->apiClient
            ->expects('makeCurlCall')
            ->with('https://api.twitch.tv/helix/videos?game_id=' . AnalyticsParameters::GAME_ID . '&sort=views&first=40', self::API_HEADERS)
            ->andReturn($topVideosResponse);
        $this->databaseClient
            ->expects('updateTopGameLastUpdateTime')
            ->with(AnalyticsParameters::GAME_ID);
        $this->databaseClient
            ->expects('updateTopGameVideos')
            ->with($topVideosResponse['response']['data'], AnalyticsParameters::GAME_ID, AnalyticsParameters::GAME_NAME);
        $this->databaseClient
            ->expects('getVideosOfAGivenGame')
            ->with(AnalyticsParameters::GAME_ID)
            ->andReturn($gameVideosResponse);

        $getTopFourtyResponse = $this->topVideosProvider->getTopFourtyVideos(
            AnalyticsParameters::GAME_ID,
            AnalyticsParameters::GAME_NAME,
            AnalyticsParameters::SINCE_TIME,
            self::API_HEADERS);

        $this->assertEquals($gameVideosResponse, $getTopFourtyResponse);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
