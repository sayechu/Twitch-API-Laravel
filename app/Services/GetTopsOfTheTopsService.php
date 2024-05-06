<?php

namespace App\Services;

class GetTopsOfTheTopsService
{
    private TopsOfTheTopsManager $topsOfTheTopsManager;

    public function __construct(TopsOfTheTopsManager $topsOfTheTopsManager)
    {
        $this->topsOfTheTopsManager = $topsOfTheTopsManager;
    }

    public function getTopsOfTheTops(int $since): array
    {
        $topsOfTheTops = $this->topsOfTheTopsManager->getTopsOfTheTops($since);

        return $topsOfTheTops;
    }
}
