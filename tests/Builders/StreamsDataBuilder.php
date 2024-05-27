<?php

namespace Tests\Builders;

use Illuminate\Http\Response;

class StreamsDataBuilder
{
    private array $response = [
        'response' => '',
        'http_code' => null
    ];

    private array $streamData = [
        'id' => '',
        'user_id' => '',
        'user_login' => '',
        'user_name' => '',
        'game_id' => '',
        'game_name' => '',
        'type' => '',
        'title' => '',
        'viewer_count' => 0,
        'started_at' => '',
        'language' => '',
        'thumbnail_url' => '',
        'tag_ids' => [],
        'tags' => [],
        'is_mature' => false
    ];

    public function withId(string $id): self
    {
        $this->streamData['id'] = $id;
        return $this;
    }

    public function withUserId(string $userId): self
    {
        $this->streamData['user_id'] = $userId;
        return $this;
    }

    public function withUserLogin(string $userLogin): self
    {
        $this->streamData['user_login'] = $userLogin;
        return $this;
    }

    public function withUserName(string $userName): self
    {
        $this->streamData['user_name'] = $userName;
        return $this;
    }

    public function withGameId(string $gameId): self
    {
        $this->streamData['game_id'] = $gameId;
        return $this;
    }

    public function withGameName(string $gameName): self
    {
        $this->streamData['game_name'] = $gameName;
        return $this;
    }

    public function withType(string $type): self
    {
        $this->streamData['type'] = $type;
        return $this;
    }

    public function withTitle(string $title): self
    {
        $this->streamData['title'] = $title;
        return $this;
    }

    public function withViewerCount(int $viewerCount): self
    {
        $this->streamData['viewer_count'] = $viewerCount;
        return $this;
    }

    public function withStartedAt(string $startedAt): self
    {
        $this->streamData['started_at'] = $startedAt;
        return $this;
    }

    public function withLanguage(string $language): self
    {
        $this->streamData['language'] = $language;
        return $this;
    }

    public function withThumbnailUrl(string $thumbnailUrl): self
    {
        $this->streamData['thumbnail_url'] = $thumbnailUrl;
        return $this;
    }

    public function withTagIds(array $tagIds): self
    {
        $this->streamData['tag_ids'] = $tagIds;
        return $this;
    }

    public function withTags(array $tags): self
    {
        $this->streamData['tags'] = $tags;
        return $this;
    }

    public function withIsMature(bool $isMature): self
    {
        $this->streamData['is_mature'] = $isMature;
        return $this;
    }

    public function withHttpCode(int $httpCode): self
    {
        $this->response['http_code'] = $httpCode;
        return $this;
    }

    public function build(): array
    {
        $this->response['response'] = json_encode(['data' => [$this->streamData]]);
        return $this->response;
    }

    public function buildExpected(): array
    {
        return [$this->streamData];
    }

    public function withTestValues(): self
    {
        return $this
            ->withId('40627613557')
            ->withUserId('92038375')
            ->withUserLogin('caedrel')
            ->withUserName('User Name')
            ->withGameId('21779')
            ->withGameName('League of Legends')
            ->withType('live')
            ->withTitle('Stream Title')
            ->withViewerCount(46181)
            ->withStartedAt('2024-05-08T07:35:07Z')
            ->withLanguage('en')
            ->withThumbnailUrl('https://static-cdn.jtvnw.net/previews-ttv/live_user_caedrel-{width}x{height}.jpg')
            ->withTagIds([])
            ->withTags(['xdd', 'Washed', 'degen', 'English', 'adhd', 'vtuber', 'Ratking', 'LPL', 'LCK', 'LEC'])
            ->withIsMature(false)
            ->withHttpCode(Response::HTTP_OK);
    }
}
