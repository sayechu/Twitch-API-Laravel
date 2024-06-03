<?php

namespace Tests\Unit\Infrastructure\Requests\FollowStreamer;

use App\Infrastructure\FollowStreamer\AnalyticsFollowStreamerRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Tests\TestCase;

class AnalyticsFollowStreamerRequestTest extends TestCase
{
    private const INVALID_STREAMER_ID_MESSAGE = 'El streamerId debe ser un número';
    private const REQUIRED_STREAMER_ID_MESSAGE = 'El streamerId es obligatorio';
    private const REQUIRED_USERNAME_MESSAGE = 'El username es obligatorio';

    /**
     * @test
     */
    public function request_streamer_data_is_invalid(): void
    {
        $request = new AnalyticsFollowStreamerRequest();
        $request->merge([
            'username' => 'username',
            'streamerId' => 'streamerId'
        ]);
        $expectedErrors = new MessageBag([
            'streamerId' => [self::INVALID_STREAMER_ID_MESSAGE],
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
    public function streamer_id_is_required_in_request(): void
    {
        $request = new AnalyticsFollowStreamerRequest();
        $request->merge([
            'username' => 'username'
        ]);
        $expectedErrors = new MessageBag([
            'streamerId' => [self::REQUIRED_STREAMER_ID_MESSAGE],
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
    public function username_is_required_in_request(): void
    {
        $request = new AnalyticsFollowStreamerRequest();
        $request->merge([
            'streamerId' => 1234
        ]);
        $expectedErrors = new MessageBag([
            'username' => [self::REQUIRED_USERNAME_MESSAGE],
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
    public function request_data_is_valid(): void
    {
        $request = new AnalyticsFollowStreamerRequest();
        $request->merge([
            'username' => 'username',
            'streamerId' => 1234
        ]);

        $validationErrors = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validationErrors->passes());
    }
}
