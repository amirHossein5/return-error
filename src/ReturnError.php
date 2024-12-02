<?php

namespace Amirhossein5\ReturnError;

use BackedEnum;
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
}
