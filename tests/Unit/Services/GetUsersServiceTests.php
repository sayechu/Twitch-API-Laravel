<?php

namespace Tests\Unit\Services;

use App\Services\GetStreamsService;
use App\Services\GetUsersService;
use App\Services\UsersManager;
use Mockery;
use Tests\TestCase;

class GetUsersServiceTests extends TestCase
{
    /**
     * @test
     */
    public function get_users_test(): void
    {
        $usersManager = Mockery::mock(UsersManager::class);
        $this->app
            ->when(GetUsersService::class)
            ->needs(UsersManager::class)
            ->give(fn() => $usersManager);
        $getUsersService = new GetUsersService($usersManager);
        $expectedResponse = json_decode('[{
            "id": "1234",
            "login": "zdraste_vladkenov",
            "display_name": "zdraste_vladkenov",
            "type": "",
            "broadcaster_type": "",
            "description": "wasde876",
            "profile_image_url": "https://static-cdn.jtvnw.net/user-default-pictures-uv/ebe4cd89-b4f4-4cd9-adac-2f30151b4209-profile_image-300x300.png",
            "offline_image_url": "",
            "view_count": 0,
            "created_at": "2018-09-04T15:23:04Z"
        }]');

        $usersManager
            ->expects('getUserInfoById')
            ->with('1234')
            ->once()
            ->andReturn($expectedResponse);

        $returnedUserInfo = $getUsersService->getUserInfoById('1234');

        $this->assertEquals($expectedResponse, $returnedUserInfo);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
