<?php

namespace App\Services;

class GetStreamsService
{
    private StreamsDataManager $streamsDataManager;

    public function __construct(StreamsDataManager $streamsDataManager)
    {
        $this->streamsDataManager = $streamsDataManager;
    }

    public function execute(): string|array
    {
        $streamsData = $this->streamsDataManager->getStreamsData();

        if ($this->isResponseAnError($streamsData)) {
            return $streamsData;
        }

        $filteredStreams = [];
        foreach ($streamsData as $stream) {
            $filteredStreams[] = [
                'title' => $stream['title'],
                'user_name' => $stream['user_name']
            ];
        }

        return $filteredStreams;
    }

    private function isResponseAnError(array $streamsData): bool
    {
        return isset($streamsData['error']);
    }
}
