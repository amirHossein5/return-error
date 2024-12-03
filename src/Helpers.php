<?php

namespace Amirhossein5\ReturnError;

use Amirhossein5\ReturnError\ReturnError;
use BackedEnum;
use Exception;
use Throwable;
use UnitEnum;

function isReturnError(mixed $value, string|UnitEnum|BackedEnum $type): bool
{
    $isA = $value instanceof ReturnError;

    if (!$isA) {
        return false;
    }

    return $value->type === $type;
}

function newReturnError(?string $message = null, null|string|UnitEnum|BackedEnum $type = null): ReturnError
{
    return new ReturnError(message: $message, type: $type);
}

function reportRE(ReturnError $re, mixed $additional = null): void
{
    report($re->toString($additional)); /** @phpstan-ignore-line */
}

/**
 * @template T
 * @param T $re
 * @return T
 */
function unwrapRE(mixed $re, mixed $additional = null): mixed
{
    if (is_a($re, ReturnError::class)) {
        throw new Exception($re->toString($additional));
    }

    return $re;
}

/**
 * @template T
 * @param callable(): T $callable
 * @return T|\Amirhossein5\ReturnError\ReturnError
 */
function wrapRE(callable $callable): mixed
{
    try {
        return $callable();
    } catch (Throwable $e) {
        return newReturnError($e->getMessage());
    }
}
