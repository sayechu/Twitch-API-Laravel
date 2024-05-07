<?php

namespace App\Services;

class GetStreamsService
{
    private StreamsManager $streamsManager;

    public function __construct(StreamsManager $streamsManager)
    {
        $this->streamsManager = $streamsManager;
    }

    public function getStreams(): array
    {
        $streams = $this->streamsManager->getStreams();

        $filteredStreams = [];
        foreach ($streams as $stream) {
            $filteredStreams[] = [
                'title' => $stream['title'],
                'user_name' => $stream['user_name']
            ];
        }

        return $filteredStreams;
    }
}
