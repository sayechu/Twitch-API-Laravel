<?php

namespace App\Infrastructure\CreateUser;

use App\Exceptions\ConflictException;
use App\Exceptions\InternalServerErrorException;
use App\Infrastructure\Controllers\Controller;
use App\Services\CreateUserManager;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsCreateUserController extends Controller
{
    private CreateUserManager $createUserManager;

    public function __construct(CreateUserManager $createUserManager)
    {
        $this->createUserManager = $createUserManager;
    }
    public function __invoke(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if (empty($username) || empty($password)) {
            return response()->json(['error' => 'Los parámetros (username y password) no fueron proporcionados.'], 400);
        }

        try {
            $createUserMessage = $this->createUserManager->getCreateUserMessage($username, $password);
            return response()->json($createUserMessage, 201);
        } catch (ConflictException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        } catch (InternalServerErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
