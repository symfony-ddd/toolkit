<?php

namespace SymfonyDDD\CoreBundle\Tests\Unit\ValueObject\Mock;

use InvalidArgumentException;
use SymfonyDDD\CoreBundle\Domain\ValueObject\IntValue;

readonly class Delay extends IntValue
{
    public static function fromMilliseconds(int $milliseconds): self
    {
        return new self($milliseconds);
    }

    protected function validate(int $value): void
    {
        assert($value >= 0, throw new InvalidArgumentException('Delay cannot be negative'));
    }
}
