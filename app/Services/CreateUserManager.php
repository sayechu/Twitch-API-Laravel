<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\InternalServerErrorException;

class CreateUserManager
{
    private DBClient $dbClient;

    public function __construct(DBClient $dbClient)
    {
        $this->dbClient = $dbClient;
    }

    /**
     * @throws ConflictException
     * @throws InternalServerErrorException
     */
    public function getCreateUserMessage($username, $password): array
    {
        $userExists = $this->dbClient->checkIfUsernameExists($username);

        if ($userExists) {
            throw new ConflictException("El nombre de usuario ya está en uso.");
        }

        $this->dbClient->createUser($username, $password);

        return [
            "username" => $username,
            "message" => "Usuario creado correctamente"
        ];
    }
}
