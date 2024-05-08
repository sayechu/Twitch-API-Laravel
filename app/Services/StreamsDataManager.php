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

        return $this->streamsDataProvider->streamsUserData($api_headers);
    }
}
