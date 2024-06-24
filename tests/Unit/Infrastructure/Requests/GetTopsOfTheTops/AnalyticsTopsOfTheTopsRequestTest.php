<?php

namespace Tests\Unit\Infrastructure\Requests\GetTopsOfTheTops;

use App\Infrastructure\GetTopsOfTheTops\AnalyticsTopsOfTheTopsRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Tests\TestCase;

class AnalyticsTopsOfTheTopsRequestTest extends TestCase
{
    /**
     * @test
     */
    public function request_is_valid(): void
    {
        $analyticsTopsRequest = new AnalyticsTopsOfTheTopsRequest();
        $analyticsTopsRequest->merge(['since' => 10]);


        $validationErrors = Validator::make(
            $analyticsTopsRequest->all(),
            $analyticsTopsRequest->rules(),
            $analyticsTopsRequest->messages());

        $this->assertTrue($validationErrors->passes());
    }

    /**
     * @test
     */
    public function request_data_not_numeric(): void
    {
        $analyticsTopsRequest = new AnalyticsTopsOfTheTopsRequest();
        $analyticsTopsRequest->merge(['since' => 'abc']);
        $expectedErrors = new MessageBag([
            'since' => ['El atributo since debe ser un entero'],
        ]);

        $validationErrors = Validator::make(
            $analyticsTopsRequest->all(),
            $analyticsTopsRequest->rules(),
            $analyticsTopsRequest->messages());

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }

    /**
     * @test
     */
    public function request_without_arguments(): void
    {
        $analyticsTopsRequest = new AnalyticsTopsOfTheTopsRequest();

        $validationErrors = Validator::make(
            $analyticsTopsRequest->all(),
            $analyticsTopsRequest->rules(),
            $analyticsTopsRequest->messages()
        );

        $this->assertTrue($validationErrors->passes());
    }

    /**
     * @test
     */
    public function request_data_invalid_integer(): void
    {
        $analyticsTopsRequest = new AnalyticsTopsOfTheTopsRequest();
        $analyticsTopsRequest->merge(['since' => 0]);
        $expectedErrors = new MessageBag([
            'since' => ['El atributo since debe ser como mínimo 1'],
        ]);

        $validationErrors = Validator::make(
            $analyticsTopsRequest->all(),
            $analyticsTopsRequest->rules(),
            $analyticsTopsRequest->messages());

        $this->assertTrue($validationErrors->fails());
        $this->assertEquals($expectedErrors, $validationErrors->errors());
    }
}
