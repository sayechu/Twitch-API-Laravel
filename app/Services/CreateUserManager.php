<?php

namespace App\Services;

use App\Exceptions\ConflictException;

class CreateUserManager
{
    private DBClient $dbClient;
    private const CREATE_USER_MESSAGE = 'Usuario creado correctamente';
    private const CONFLICT_ERROR_MESSAGE = 'El nombre de usuario ya está en uso.';

    public function __construct(DBClient $dbClient)
    {
        $this->dbClient = $dbClient;
    }

    public function getCreateUserMessage($username, $password): array
    {
        $userExists = $this->dbClient->checkIfUsernameExists($username);

        if ($userExists) {
            throw new ConflictException(self::CONFLICT_ERROR_MESSAGE);
        }

        $this->dbClient->createUser($username, $password);

        return [
            "username" => $username,
            "message" => self::CREATE_USER_MESSAGE
        ];
    }
}
