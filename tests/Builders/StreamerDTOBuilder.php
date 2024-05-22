<?php

namespace Tests\Builders;

class StreamerDTOBuilder
{
    private StreamerDTO $streamerDTO;

    public function __construct()
    {
        $this->streamerDTO = new StreamerDTO();
    }

    public function withId(string $id) { $this->streamerDTO->setId($id); return $this; }
    public function withLogin(string $login) { $this->streamerDTO->setLogin($login); return $this; }
    public function withDisplayName(string $displayName) { $this->streamerDTO->setDisplayName($displayName); return $this; }
    public function withType(string $type) { $this->streamerDTO->setType($type); return $this; }
    public function withBroadcasterType(string $broadcasterType) { $this->streamerDTO->setBroadcasterType($broadcasterType); return $this; }
    public function withDescription(string $description) { $this->streamerDTO->setDescription($description); return $this; }
    public function withProfileImageUrl(string $profileImageUrl) { $this->streamerDTO->setProfileImageUrl($profileImageUrl); return $this; }
    public function withOfflineImageUrl(string $offlineImageUrl) { $this->streamerDTO->setOfflineImageUrl($offlineImageUrl); return $this; }
    public function withViewCount(int $viewCount) { $this->streamerDTO->setViewCount($viewCount); return $this; }
    public function withCreatedAt(string $createdAt) { $this->streamerDTO->setCreatedAt($createdAt); return $this; }

    public function build(): StreamerDTO
    {
        return $this->streamerDTO;
    }
}
