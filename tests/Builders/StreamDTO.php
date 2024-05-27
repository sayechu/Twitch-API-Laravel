<?php

namespace Tests\Builders;

class StreamDTO
{
    public string $id;
    public string $userId;
    public string $userLogin;
    public string $userName;
    public string $gameId;
    public string $gameName;
    public string $type;
    public string $title;
    public string $viewerCount;
    public string $startedAt;
    public string $language;
    public string $thumbnailUrl;
    public array $tagsId = [];
    public array $tags = [];
    public string $isMature;

    public function setId($id) { $this->id = $id; }
    public function setUserId($userId) { $this->userId = $userId; }
    public function setUserLogin($userLogin) { $this->userLogin = $userLogin; }
    public function setUserName($userName) { $this->userName = $userName; }
    public function setGameId($gameId) { $this->gameId = $gameId; }
    public function setGameName($gameName) { $this->gameName = $gameName; }
    public function setType(string $type) { $this->type = $type; }
    public function setTitle(string $title) { $this->title = $title; }
    public function setViewerCount(string $viewerCount) { $this->viewerCount = $viewerCount; }
    public function setStartedAt(string $startedAt) { $this->startedAt = $startedAt; }
    public function setLanguage(string $language) { $this->language = $language; }
    public function setThumbnailUrl(string $thumbnailUrl) { $this->thumbnailUrl = $thumbnailUrl; }
    public function setTags(array $tags) { $this->tags = $tags; }
    public function setIsMature(string $isMature) { $this->isMature = $isMature; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'user_login' => $this->userLogin,
            'user_name' => $this->userName,
            'game_id' => $this->gameId,
            'game_name' => $this->gameName,
            'type' => $this->type,
            'title' => $this->title,
            'viewer_count' => $this->viewerCount,
            'started_at' => $this->startedAt,
            'language' => $this->language,
            'thumbnail_url' => $this->thumbnailUrl,
            'tag_ids' => $this->tagsId,
            'tags' => $this->tags,
            'is_mature' => $this->isMature
        ];
    }
}
