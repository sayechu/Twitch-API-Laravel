<?php

namespace App\Services;

class GetTopsOfTheTopsService
{
    private TopsOfTheTopsManager $topsOfTheTopsManager;

    public function __construct(TopsOfTheTopsManager $topsOfTheTopsManager)
    {
        $this->topsOfTheTopsManager = $topsOfTheTopsManager;
    }

    public function getTop40VideosDadoUnGameId($gameId) : array
    {
        return $this->topsOfTheTopsManager->getTop40VideosDadoUnGameId($gameId);
    }

    public function getTopGames() : array
    {
        return $this->topsOfTheTopsManager->getTopGames();
    }
}
