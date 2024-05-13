<?php

namespace Tests\Unit\Infrastructure\Requests;

use App\Infrastructure\GetUsers\AnalyticsUsersRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Tests\TestCase;

class AnalyticsUsersRequestTest extends TestCase
{
    /**
     * @test
     */
    public function request_data_is_invalid(): void
    {
        $analyticsUsersRequest = new AnalyticsUsersRequest();
        $analyticsUsersRequest->merge(['id' => 'abc']);
        $expectedErrors = new MessageBag([
            'id' => ['El Id dado no es válido'],
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
        $analyticsUsersRequest = new AnalyticsUsersRequest();
        $expectedErrors = new MessageBag([
            'id' => ['El Id es obligatorio'],
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
        $analyticsUsersRequest = new AnalyticsUsersRequest();
        $analyticsUsersRequest->merge(['id' => 1234]);

        $validationErrors = Validator::make(
            $analyticsUsersRequest->all(),
            $analyticsUsersRequest->rules(),
            $analyticsUsersRequest->messages());

        $this->assertTrue($validationErrors->passes());
    }
}
