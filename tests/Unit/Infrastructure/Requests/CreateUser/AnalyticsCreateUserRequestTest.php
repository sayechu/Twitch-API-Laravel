<?php

namespace Tests\Unit\Infrastructure\Requests\CreateUser;

use App\Infrastructure\CreateUser\AnalyticsCreateUserRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Tests\TestCase;

class AnalyticsCreateUserRequestTest extends TestCase
{
    private const REQUIRED_USERNAME_MESSAGE = 'El username es obligatorio';
    private const INVALID_USERNAME_MESSAGE = 'El username debe ser una cadena de texto';
    private const REQUIRED_PASSWORD_MESSAGE = 'La password es obligatoria';
    private const INVALID_PASSWORD_MESSAGE = 'La password debe ser una cadena de texto';

    /**
     * @test
     */
    public function request_data_is_valid(): void
    {
        $request = new AnalyticsCreateUserRequest();
        $request->merge([
            'username' => 'username',
            'password' => 'password'
        ]);

        $validationErrors = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validationErrors->passes());
    }

    /**
     * @test
     */
    public function request_required_username_required_password(): void
    {
        $request = new AnalyticsCreateUserRequest();
        $request->merge([
            'username' => '',
            'password' => ''
        ]);
        $expectedErrors = new MessageBag([
            'username' => [self::REQUIRED_USERNAME_MESSAGE],
            'password' => [self::REQUIRED_PASSWORD_MESSAGE],
        ]);

        $validationErrors = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }

    /**
     * @test
     */
    public function request_required_username_invalid_password(): void
    {
        $request = new AnalyticsCreateUserRequest();
        $request->merge([
            'username' => '',
            'password' => 1234
        ]);
        $expectedErrors = new MessageBag([
            'username' => [self::REQUIRED_USERNAME_MESSAGE],
            'password' => [self::INVALID_PASSWORD_MESSAGE],
        ]);

        $validationErrors = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }

    /**
     * @test
     */
    public function request_invalid_username_invalid_password(): void
    {
        $request = new AnalyticsCreateUserRequest();
        $request->merge([
            'username' => 1234,
            'password' => 1234
        ]);
        $expectedErrors = new MessageBag([
            'username' => [self::INVALID_USERNAME_MESSAGE],
            'password' => [self::INVALID_PASSWORD_MESSAGE],
        ]);

        $validationErrors = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }
}
