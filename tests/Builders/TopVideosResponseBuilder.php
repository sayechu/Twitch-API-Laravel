<?php

namespace Tests\Builders;

class TopVideosResponseBuilder
{
    private array $response = [];

    public function withTestValues(): self
    {
        $this->response = [
            [
                [
                    'id' => 621881727,
                    'user_id' => 31919607,
                    'user_name' => 'elxokas',
                    'view_count' => 3525329,
                    'duration' => '59s',
                    'created_at' => '2020-05-15T17:27:17Z',
                    'title' => 'TRAILER DE JESUCRISTO',
                    'game_id' => '509658',
                    'game_name' => 'Just Chatting'
                ]
            ],
            [
                [
                    'id' => 694878525,
                    'user_id' => 506590738,
                    'user_name' => 'NOBRU',
                    'view_count' => 1062419,
                    'duration' => '2h33m34s',
                    'created_at' => '2020-07-30T02:05:03Z',
                    'title' => 'ASSALTO AO BANCO CENTRAL - RP🔥',
                    'game_id' => '32982',
                    'game_name' => 'Grand Theft Auto V'
                ]
            ],
            [
                [
                    'id' => 922796862,
                    'user_id' => 31239503,
                    'user_name' => 'ESLCS',
                    'view_count' => 4496031,
                    'duration' => '12h37m37s',
                    'created_at' => '2021-02-21T10:02:28Z',
                    'title' => 'RERUN: G2 Esports vs. Evil Geniuses [Dust2] Map 2 - IEM Katowice 2021 - Group A',
                    'game_id' => '32399',
                    'game_name' => 'Counter-Strike'
                ]
            ]
        ];
        return $this;
    }

    public function build(): array
    {
        return $this->response;
    }

    public function buildExpected(): array
    {
        return [
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
            ],
            [
                'game_id' => '32982',
                'game_name' => 'Grand Theft Auto V',
                'user_name' => 'NOBRU',
                'total_videos' => '1',
                'total_views' => '1062419',
                'most_viewed_title' => 'ASSALTO AO BANCO CENTRAL - RP🔥',
                'most_viewed_views' => '1062419',
                'most_viewed_duration' => '2h33m34s',
                'most_viewed_created_at' => '2020-07-30T02:05:03Z'
            ],
            [
                'game_id' => '32399',
                'game_name' => 'Counter-Strike',
                'user_name' => 'ESLCS',
                'total_videos' => '1',
                'total_views' => '4496031',
                'most_viewed_title' => 'RERUN: G2 Esports vs. Evil Geniuses [Dust2] Map 2 - IEM Katowice 2021 - Group A',
                'most_viewed_views' => '4496031',
                'most_viewed_duration' => '12h37m37s',
                'most_viewed_created_at' => '2021-02-21T10:02:28Z'
            ]
        ];
    }
}
