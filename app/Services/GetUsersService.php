<?php

namespace App\Services;

class GetUsersService
{
    private UsersManager $usersManager;

    public function __construct(UsersManager $usersManager)
    {
        $this->usersManager = $usersManager;
    }

    public function getUserInfoById(string $userId): array
    {
        return $this->usersManager->getUserInfoById($userId);
    }
}
