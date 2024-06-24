<?php

namespace Tests\Unit\Infrastructure\Requests;

use App\Infrastructure\Timeline\AnalyticsTimelineRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Tests\TestCase;

class AnalyticsTimelineRequestTest extends TestCase
{
    private const INVALID_ARGUMENT_MESSAGE = 'El username debe ser una cadena de texto';
    private const REQUIRED_ARGUMENT_MESSAGE = 'El username es obligatorio';

    /**
     * @test
     */
    public function request_data_is_invalid_when_username_is_not_string(): void
    {
        $analyticsTimelineRequest = new AnalyticsTimelineRequest();
        $analyticsTimelineRequest->merge(['username' => 123]);
        $expectedErrors = new MessageBag([
            'username' => [self::INVALID_ARGUMENT_MESSAGE],
        ]);

        $validationErrors = Validator::make(
            $analyticsTimelineRequest->all(),
            $analyticsTimelineRequest->rules(),
            $analyticsTimelineRequest->messages()
        );

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }

    /**
     * @test
     */
    public function username_is_required_in_request(): void
    {
        $analyticsTimelineRequest = new AnalyticsTimelineRequest();
        $expectedErrors = new MessageBag([
            'username' => [self::REQUIRED_ARGUMENT_MESSAGE],
        ]);

        $validationErrors = Validator::make(
            $analyticsTimelineRequest->all(),
            $analyticsTimelineRequest->rules(),
            $analyticsTimelineRequest->messages()
        );

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }

    /**
     * @test
     */
    public function username_is_valid(): void
    {
        $analyticsTimelineRequest = new AnalyticsTimelineRequest();
        $analyticsTimelineRequest->merge(['username' => 'username']);

        $validationErrors = Validator::make(
            $analyticsTimelineRequest->all(),
            $analyticsTimelineRequest->rules(),
            $analyticsTimelineRequest->messages()
        );

        $this->assertTrue($validationErrors->passes());
    }
}
