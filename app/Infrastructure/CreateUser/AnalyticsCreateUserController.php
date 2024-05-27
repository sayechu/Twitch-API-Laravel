<?php

namespace App\Infrastructure\CreateUser;

use App\Exceptions\ConflictException;
use App\Exceptions\InternalServerErrorException;
use App\Infrastructure\Controllers\Controller;
use App\Services\CreateUserManager;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsCreateUserController extends Controller
{
    private CreateUserManager $createUserManager;
    private const INTERNAL_SERVER_ERROR = "Error del servidor al crear el usuario";
    private const CONFLICT_ERROR = "El nombre de usuario ya está en uso.";
    private const PARAMETERS_ERROR = 'Los parámetros (username y password) no fueron proporcionados.';

    public function __construct(CreateUserManager $createUserManager)
    {
        $this->createUserManager = $createUserManager;
    }
    public function __invoke(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if (empty($username) || empty($password)) {
            return response()->json(
                ['error' => self::PARAMETERS_ERROR],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $createUserMessage = $this->createUserManager->getCreateUserMessage($username, $password);
            return response()->json($createUserMessage, Response::HTTP_CREATED);
        } catch (ConflictException) {
            return response()->json(['error' => self::CONFLICT_ERROR], Response::HTTP_CONFLICT);
        } catch (InternalServerErrorException) {
            return response()->json(['error' => self::INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
