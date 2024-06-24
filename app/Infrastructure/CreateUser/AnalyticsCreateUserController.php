<?php

namespace App\Infrastructure\CreateUser;

use App\Exceptions\ConflictException;
use App\Exceptions\InternalServerErrorException;
use App\Infrastructure\Controllers\Controller;
use App\Services\CreateUserManager;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class AnalyticsCreateUserController extends Controller
{
    private CreateUserManager $createUserManager;

    public function __construct(CreateUserManager $createUserManager)
    {
        $this->createUserManager = $createUserManager;
    }
    public function __invoke(AnalyticsCreateUserRequest $request): JsonResponse
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if (empty($username) || empty($password)) {
            return response()->json(
                ['error' => 'Los parámetros (username y password) no fueron proporcionados.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $createUserMessage = $this->createUserManager->getCreateUserMessage($username, $password);
            return response()->json($createUserMessage, Response::HTTP_CREATED);
        } catch (ConflictException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (InternalServerErrorException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
