<?php

namespace App\Services;

use Illuminate\Http\Response;

class GetStreamsService
{
    private StreamsDataManager $streamsDataManager;

    public function __construct(StreamsDataManager $streamsDataManager)
    {
        $this->streamsDataManager = $streamsDataManager;
    }

    public function execute(): array | string
    {
        $streamsData = $this->streamsDataManager->getStreamsData();

        if ($this->tokenProviderReturns500($streamsData)) {
            return $streamsData;
        }

        if ($this->isA500Code($streamsData['http_code'])) {
            return '503: {"error": "No se pueden devolver streams en este momento, inténtalo más tarde"}';
        }

        $streamsDataResponse =  json_decode($streamsData['response'], true)['data'];

        $filteredStreams = [];
        foreach ($streamsDataResponse as $stream) {
            $filteredStreams[] = [
                'title' => $stream['title'],
                'user_name' => $stream['user_name']
            ];
        }

        return $filteredStreams;
    }

    private function isA500Code(int $http_code): bool
    {
        return $http_code === Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function tokenProviderReturns500(mixed $tokenProviderResponse): bool
    {
        return $tokenProviderResponse === '503: {"error": "No se puede establecer conexión con Twitch en este momento}';
    }
}
