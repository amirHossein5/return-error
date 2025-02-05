<?php

namespace Tests\Feature;

use Amirhossein5\ReturnError\ReturnError;
use Exception;
use Tests\TestCase;

enum Errors
{
    case EXAMPLE;
}

enum ErrorsB: string
{
    case EXAMPLE = 'example';
}

class ReturnErrorTest extends TestCase
{
    public function test_new_return_error(): void
    {
        $returnError = new ReturnError();
        $this->assertFalse($returnError->type === Errors::EXAMPLE);

        $returnError = new ReturnError(type: 'sometype');
        $this->assertTrue($returnError->type === 'sometype');
        $this->assertFalse($returnError->type === Errors::EXAMPLE);

        $returnError = new ReturnError(type: Errors::EXAMPLE);
        $this->assertTrue($returnError->type === Errors::EXAMPLE);
    }

    public function test_error_message(): void
    {
        $this->assertThrows(function () {
            ReturnError::unwrap(new ReturnError());
        }, expectedMessage: '');

        $this->assertThrows(function () {
            ReturnError::unwrap(new ReturnError('some message'));
        }, expectedMessage: 'message: some message');

        $this->assertThrows(function () {
            ReturnError::unwrap(new ReturnError(type: 'some type'));
        }, expectedMessage: 'type: some type');
        $this->assertThrows(function () {
            ReturnError::unwrap(new ReturnError(type: Errors::EXAMPLE));
        }, expectedMessage: 'type: EXAMPLE');
        $this->assertThrows(function () {
            ReturnError::unwrap(new ReturnError(type: ErrorsB::EXAMPLE));
        }, expectedMessage: 'type: example');

        $this->assertThrows(function () {
            ReturnError::unwrap(new ReturnError(message: 'test message', type: 'some type'));
        }, expectedMessage: 'message: test message, type: some type');
        $this->assertThrows(function () {
            ReturnError::unwrap(new ReturnError(message: 'test message', type: Errors::EXAMPLE));
        }, expectedMessage: 'message: test message, type: EXAMPLE');
        $this->assertThrows(function () {
            ReturnError::unwrap(new ReturnError(message: 'test message', type: ErrorsB::EXAMPLE));
        }, expectedMessage: 'message: test message, type: example');
    }

    public function test_error_message_with_additional_log_data(): void
    {
        $payloads = [
            [
                'arg' => 'test',
                'exp' => json_encode('test'),
            ],
            [
                'arg' => ['test' => 'value'],
                'exp' => json_encode(['test' => 'value']),
            ],
        ];

        foreach ($payloads as $payload) {
            $this->assertThrows(function () use ($payload) {
                ReturnError::unwrap(new ReturnError(), $payload['arg']);
            }, expectedMessage: "additional: {$payload['exp']}");

            $this->assertThrows(function () use ($payload) {
                ReturnError::unwrap(new ReturnError('some message'), $payload['arg']);
            }, expectedMessage: "message: some message, additional: {$payload['exp']}");

            $this->assertThrows(function () use ($payload) {
                ReturnError::unwrap(new ReturnError(type: 'some type'), $payload['arg']);
            }, expectedMessage: "type: some type, additional: {$payload['exp']}");
            $this->assertThrows(function () use ($payload) {
                ReturnError::unwrap(new ReturnError(type: Errors::EXAMPLE), $payload['arg']);
            }, expectedMessage: "type: EXAMPLE, additional: {$payload['exp']}");
            $this->assertThrows(function () use ($payload) {
                ReturnError::unwrap(new ReturnError(type: ErrorsB::EXAMPLE), $payload['arg']);
            }, expectedMessage: "type: example, additional: {$payload['exp']}");

            $this->assertThrows(function () use ($payload) {
                ReturnError::unwrap(new ReturnError(message: 'test message', type: 'some type'), $payload['arg']);
            }, expectedMessage: "message: test message, type: some type, additional: {$payload['exp']}");
            $this->assertThrows(function () use ($payload) {
                ReturnError::unwrap(new ReturnError(message: 'test message', type: Errors::EXAMPLE), $payload['arg']);
            }, expectedMessage: "message: test message, type: EXAMPLE, additional: {$payload['exp']}");
            $this->assertThrows(function () use ($payload) {
                ReturnError::unwrap(new ReturnError(message: 'test message', type: ErrorsB::EXAMPLE), $payload['arg']);
            }, expectedMessage: "message: test message, type: example, additional: {$payload['exp']}");
        }
    }

    public function test_wraps_exception(): void
    {
        $res = ReturnError::wrap(function (): int {
            throw new Exception("some error message");
            return 2;
        });
        $this->assertTrue($res instanceof ReturnError);
        $this->assertEquals($res->toString(), 'message: some error message');

        $res = ReturnError::wrap(function (): int {
            return 2;
        });
        $this->assertFalse($res instanceof ReturnError);
        $this->assertTrue($res === 2);
    }

    // if (isReturnError($someCalculationResult, $e = SomeCalculationErrorTypes::DIVISION_BY_ZERO)) {
    //     reportRE($someCalculationResult, ['given' => 0]);
    //     return;
    // } elseif (is_a($someCalculationResult, ReturnError::class)) {
    //     return;
    // }
    //
    // $someCalculationResult;
    // private function someCalculation(int $divideBy): int|ReturnError
    // {
    //     if ($divideBy === 0) {
    //         return newReturnError(
    //             message: 'cant divide by zero',
    //             type: SomeCalculationErrorTypes::DIVISION_BY_ZERO,
    //         );
    //     }
    //
    //     return 2 / $divideBy;
    // }
}

enum SomeCalculationErrorTypes
{
    case DIVISION_BY_ZERO;
}
