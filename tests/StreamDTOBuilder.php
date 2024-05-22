<?php

namespace Tests;

class StreamDTOBuilder
{
    private StreamDTO $streamDTO;

    public function __construct()
    {
        $this->streamDTO = new StreamDTO();
    }

    public function withId($id) { $this->streamDTO->setId($id); return $this; }
    public function withUserId($userId) { $this->streamDTO->setUserId($userId); return $this; }
    public function withUserLogin($userLogin) { $this->streamDTO->setUserLogin($userLogin); return $this; }
    public function withUserName($userName) { $this->streamDTO->setUserName($userName); return $this; }
    public function withGameId($gameId) { $this->streamDTO->setGameId($gameId); return $this; }
    public function withGameName($gameName) { $this->streamDTO->setGameName($gameName); return $this; }
    public function withType($type) { $this->streamDTO->setType($type); return $this; }
    public function withTitle($title) { $this->streamDTO->setTitle($title); return $this; }
    public function withViewerCount($viewerCount) { $this->streamDTO->setViewerCount($viewerCount); return $this; }
    public function withStartedAt($startedAt) { $this->streamDTO->setStartedAt($startedAt); return $this; }
    public function withLanguage($language) { $this->streamDTO->setLanguage($language); return $this; }
    public function withThumbnailUrl($thumbnailUrl) { $this->streamDTO->setThumbnailUrl($thumbnailUrl); return $this; }
    public function withTags($tags) { $this->streamDTO->setTags($tags); return $this; }
    public function withIsMature($isMature) { $this->streamDTO->setIsMature($isMature); return $this; }

    public function build(): StreamDTO
    {
        return $this->streamDTO;
    }
}
