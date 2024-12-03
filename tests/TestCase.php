<?php

namespace Tests;

use Closure;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Throwable;

abstract class TestCase extends FrameworkTestCase
{
    protected function assertThrows(Closure $test, string $expectedClass = Throwable::class, ?string $expectedMessage = null): self
    {
        try {
            $test();

            $thrown = false;
        } catch (Throwable $exception) {
            $thrown = $exception instanceof $expectedClass;

            $actualMessage = $exception->getMessage();
        }

        Assert::assertTrue(
            $thrown,
            sprintf('Failed asserting that exception of type "%s" was thrown.', $expectedClass)
        );

        if (isset($expectedMessage)) {
            if (! isset($actualMessage)) {
                Assert::fail(
                    sprintf(
                        'Failed asserting that exception of type "%s" with message "%s" was thrown.',
                        $expectedClass,
                        $expectedMessage
                    )
                );
            } else {
                Assert::assertStringContainsString($expectedMessage, $actualMessage);
            }
        }

        return $this;
    }
}
