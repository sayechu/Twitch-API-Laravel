<?php

namespace Tests\Builders;

use Illuminate\Http\Response;

class StreamerDataBuilder
{
    private array $response = [
        'response' => '',
        'http_code' => null
    ];

    private array $userData = [
        'id' => '',
        'login' => '',
        'display_name' => '',
        'type' => '',
        'broadcaster_type' => '',
        'description' => '',
        'profile_image_url' => '',
        'offline_image_url' => '',
        'view_count' => 0,
        'created_at' => ''
    ];

    public function withId(string $id): self
    {
        $this->userData['id'] = $id;
        return $this;
    }

    public function withLogin(string $login): self
    {
        $this->userData['login'] = $login;
        return $this;
    }

    public function withDisplayName(string $displayName): self
    {
        $this->userData['display_name'] = $displayName;
        return $this;
    }

    public function withType(string $type): self
    {
        $this->userData['type'] = $type;
        return $this;
    }

    public function withBroadcasterType(string $broadcasterType): self
    {
        $this->userData['broadcaster_type'] = $broadcasterType;
        return $this;
    }

    public function withDescription(string $description): self
    {
        $this->userData['description'] = $description;
        return $this;
    }

    public function withProfileImageUrl(string $profileImageUrl): self
    {
        $this->userData['profile_image_url'] = $profileImageUrl;
        return $this;
    }

    public function withOfflineImageUrl(string $offlineImageUrl): self
    {
        $this->userData['offline_image_url'] = $offlineImageUrl;
        return $this;
    }

    public function withViewCount(int $viewCount): self
    {
        $this->userData['view_count'] = $viewCount;
        return $this;
    }

    public function withCreatedAt(string $createdAt): self
    {
        $this->userData['created_at'] = $createdAt;
        return $this;
    }

    public function withHttpCode(int $httpCode): self
    {
        $this->response['http_code'] = $httpCode;
        return $this;
    }

    public function build(): array
    {
        $this->response['response'] = ['data' => [$this->userData]];
        return $this->response;
    }

    public function buildExpected(): array
    {
        return ['data' => [$this->userData]];
    }

    public function withTestValues(): self
    {
        return $this
            ->withId('1234')
            ->withLogin('zdraste_vladkenov')
            ->withDisplayName('zdraste_vladkenov')
            ->withType('')
            ->withBroadcasterType('')
            ->withDescription('wasde876')
            ->withProfileImageUrl('https://static-cdn.jtvnw.net/user-default-pictures-uv/ebe4cd89-b4f4-4cd9-adac-2f30151b4209-profile_image-300x300.png')
            ->withOfflineImageUrl('')
            ->withViewCount(0)
            ->withCreatedAt('2018-09-04T15:23:04Z')
            ->withHttpCode(Response::HTTP_OK);
    }
}
