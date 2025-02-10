<?php

namespace Amirhossein5\ReturnError;

use BackedEnum;
use Exception;
use Throwable;
use UnitEnum;

class ReturnError
{
    public function __construct(
        public ?string $message = null,
        public null|string|UnitEnum|BackedEnum $type = null,
    ) {}

    public function toString(mixed $additionalLogData = null): string
    {
        $message = '';

        if ($this->message != null) {
            $message .= "message: {$this->message}, ";
        }

        if ($this->type !== null) {
            if (is_string($this->type)) {
                $message .= "type: {$this->type}";
            } else if (is_a($this->type, BackedEnum::class)) {
                $message .= "type: {$this->type->value}";
            } else if (is_a($this->type, UnitEnum::class)) {
                $message .= "type: {$this->type->name}";
            }

            $message .= ', ';
        }

        if (!is_null($additionalLogData)) {
            $message .= 'additional: ' . json_encode($additionalLogData) . ', ';
        }

        return trim($message, ', ');
    }

    public function report(mixed $additional = null): void
    {
        /** @phpstan-ignore-next-line */
        report($this->toString($additional));
    }

    /**
     * @template T
     * @param callable(): T $callable
     * @return T|\Amirhossein5\ReturnError\ReturnError
     */
    public static function wrap(callable $callable): mixed
    {
        try {
            return $callable();
        } catch (Throwable $e) {
            return new self($e->getMessage());
        }
    }

    /**
     * @template T
     * @param T $re
     * @return T
     */
    public static function unwrap(mixed $re, mixed $additional = null): mixed
    {
        if ($re instanceof ReturnError) {
            throw new Exception($re->toString($additional));
        }

        return $re;
    }
}
