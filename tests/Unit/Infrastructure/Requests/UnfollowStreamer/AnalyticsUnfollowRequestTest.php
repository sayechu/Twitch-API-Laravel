<?php

namespace Tests\Unit\Infrastructure\Requests\UnfollowStreamer;

use App\Infrastructure\UnfollowStreamer\AnalyticsUnfollowRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Tests\TestCase;

class AnalyticsUnfollowRequestTest extends TestCase
{
    private const REQUIRED_USERNAME_MESSAGE = 'El nombre de usuario es obligatorio';
    private const INVALID_USERNAME_MESSAGE = 'El nombre de usuario debe ser una cadena de caracteres';
    private const REQUIRED_STREAMERID_MESSAGE = 'El ID del streamer es obligatorio';
    private const INVALID_STREAMERID_MESSAGE = 'El ID del streamer debe ser un número entero';

    /**
     * @test
     */
    public function request_is_valid(): void
    {
        $unfollowRequest = new AnalyticsUnfollowRequest();
        $unfollowRequest->merge(['username' => 'username', 'streamerId' => 1234]);

        $validationErrors = Validator::make(
            $unfollowRequest->all(),
            $unfollowRequest->rules(),
            $unfollowRequest->messages()
        );

        $this->assertTrue($validationErrors->passes());
    }

    /**
     * @test
     */
    public function request_username_invalid_streamerid_invalid(): void
    {
        $unfollowRequest = new AnalyticsUnfollowRequest();
        $unfollowRequest->merge(['username' => 123, 'streamerId' => 'abc']);
        $expectedErrors = new MessageBag([
            'username' => [self::INVALID_USERNAME_MESSAGE],
            'streamerId' => [self::INVALID_STREAMERID_MESSAGE],
        ]);

        $validationErrors = Validator::make(
            $unfollowRequest->all(),
            $unfollowRequest->rules(),
            $unfollowRequest->messages()
        );

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }

    /**
     * @test
     */
    public function request_username_required_streamerid_required(): void
    {
        $unfollowRequest = new AnalyticsUnfollowRequest();
        $unfollowRequest->merge(['username' => '', 'streamerId' => '']);
        $expectedErrors = new MessageBag([
            'username' => [self::REQUIRED_USERNAME_MESSAGE],
            'streamerId' => [self::REQUIRED_STREAMERID_MESSAGE],
        ]);

        $validationErrors = Validator::make(
            $unfollowRequest->all(),
            $unfollowRequest->rules(),
            $unfollowRequest->messages()
        );

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }

    /**
     * @test
     */
    public function request_username_invalid_streamerid_required(): void
    {
        $unfollowRequest = new AnalyticsUnfollowRequest();
        $unfollowRequest->merge(['username' => 123, 'streamerId' => '']);
        $expectedErrors = new MessageBag([
            'username' => [self::INVALID_USERNAME_MESSAGE],
            'streamerId' => [self::REQUIRED_STREAMERID_MESSAGE],
        ]);

        $validationErrors = Validator::make(
            $unfollowRequest->all(),
            $unfollowRequest->rules(),
            $unfollowRequest->messages()
        );

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }

    /**
     * @test
     */
    public function request_username_required_streamerid_invalid(): void
    {
        $unfollowRequest = new AnalyticsUnfollowRequest();
        $unfollowRequest->merge(['username' => '', 'streamerId' => 'abc']);
        $expectedErrors = new MessageBag([
            'username' => [self::REQUIRED_USERNAME_MESSAGE],
            'streamerId' => [self::INVALID_STREAMERID_MESSAGE],
        ]);

        $validationErrors = Validator::make(
            $unfollowRequest->all(),
            $unfollowRequest->rules(),
            $unfollowRequest->messages()
        );

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }

}
