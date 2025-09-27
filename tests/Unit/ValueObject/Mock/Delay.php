<?php

namespace SymfonyDDD\ToolkitBundle\Tests\Unit\ValueObject\Mock;

use InvalidArgumentException;
use SymfonyDDD\ToolkitBundle\library\Value\IntValue;

readonly class Delay extends IntValue
{
    public static function fromMilliseconds(int $milliseconds): self
    {
        return new self($milliseconds);
    }

    protected function validate(int $value): void
    {
        assert($value >= 0, new InvalidArgumentException('Delay cannot be negative'));
    }
}
