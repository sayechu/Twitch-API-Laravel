<?php

namespace Tests\Builders;

class StreamerDTO
{
    public string $id;
    public string $login;
    public string $displayName;
    public string $type;
    public string $broadcasterType;
    public string $description;
    public string $profileImageUrl;
    public string $offlineImageUrl;
    public int $viewCount;
    public string $createdAt;

    public function setId(string $id) { $this->id = $id; }
    public function setLogin(string $login) { $this->login = $login; }
    public function setDisplayName(string $displayName) { $this->displayName = $displayName; }
    public function setType(string $type) { $this->type = $type; }
    public function setBroadcasterType(string $broadcasterType) { $this->broadcasterType = $broadcasterType; }
    public function setDescription(string $description) { $this->description = $description; }
    public function setProfileImageUrl(string $profileImageUrl) { $this->profileImageUrl = $profileImageUrl; }
    public function setOfflineImageUrl(string $offlineImageUrl) { $this->offlineImageUrl = $offlineImageUrl; }
    public function setViewCount(int $viewCount) { $this->viewCount = $viewCount; }
    public function setCreatedAt(string $createdAt) { $this->createdAt = $createdAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'display_name' => $this->displayName,
            'type' => $this->type,
            'broadcaster_type' => $this->broadcasterType,
            'description' => $this->description,
            'profile_image_url' => $this->profileImageUrl,
            'offline_image_url' => $this->offlineImageUrl,
            'view_count' => $this->viewCount,
            'created_at' => $this->createdAt,
        ];
    }
}
