<?php

namespace App\Services;

class StreamsDataManager
{
    private TokenProvider $tokenProvider;
    private StreamsDataProvider $streamsDataProvider;

    public function __construct(TokenProvider $tokenProvider, StreamsDataProvider $streamsDataProvider)
    {
        $this->tokenProvider = $tokenProvider;
        $this->streamsDataProvider = $streamsDataProvider;
    }

    public function getStreamsData(): array
    {
        $api_headers = array('Authorization: Bearer ' . $this->tokenProvider->getToken());

        $streamsData = $this->streamsDataProvider->streamsUserData($api_headers);

        $filteredStreams = [];
        foreach ($streamsData as $stream) {
            $filteredStreams[] = [
                'title' => $stream['title'],
                'user_name' => $stream['user_name']
            ];
        }

        return $filteredStreams;
    }
}
