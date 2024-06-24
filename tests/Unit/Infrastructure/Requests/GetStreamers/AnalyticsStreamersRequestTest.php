<?php

namespace Tests\Unit\Infrastructure\Requests\GetStreamers;

use App\Infrastructure\GetStreamers\AnalyticsStreamersRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Tests\TestCase;

class AnalyticsStreamersRequestTest extends TestCase
{
    private const INVALID_ARGUMENT_MESSAGE = 'El Id dado no es válido';
    private const REQUIRED_ARGUMENT_MESSAGE = 'El Id es obligatorio';

    /**
     * @test
     */
    public function request_data_is_invalid(): void
    {
        $analyticsUsersRequest = new AnalyticsStreamersRequest();
        $analyticsUsersRequest->merge(['id' => 'abc']);
        $expectedErrors = new MessageBag([
            'id' => [self::INVALID_ARGUMENT_MESSAGE],
        ]);

        $validationErrors = Validator::make(
            $analyticsUsersRequest->all(),
            $analyticsUsersRequest->rules(),
            $analyticsUsersRequest->messages());

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }

    /**
     * @test
     */
    public function id_required_in_request(): void
    {
        $analyticsUsersRequest = new AnalyticsStreamersRequest();
        $expectedErrors = new MessageBag([
            'id' => [self::REQUIRED_ARGUMENT_MESSAGE],
        ]);

        $validationErrors = Validator::make(
            $analyticsUsersRequest->all(),
            $analyticsUsersRequest->rules(),
            $analyticsUsersRequest->messages());

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }

    /**
     * @test
     */
    public function id_is_valid(): void
    {
        $analyticsUsersRequest = new AnalyticsStreamersRequest();
        $analyticsUsersRequest->merge(['id' => 1234]);

        $validationErrors = Validator::make(
            $analyticsUsersRequest->all(),
            $analyticsUsersRequest->rules(),
            $analyticsUsersRequest->messages());

        $this->assertTrue($validationErrors->passes());
    }
}
