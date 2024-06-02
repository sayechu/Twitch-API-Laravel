<?php

namespace App\Services;

class GetStreamsService
{
    private StreamsDataManager $streamsDataManager;

    public function __construct(StreamsDataManager $streamsDataManager)
    {
        $this->streamsDataManager = $streamsDataManager;
    }

    public function execute(): array
    {
        $streamsData = $this->streamsDataManager->getStreamsData();

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
