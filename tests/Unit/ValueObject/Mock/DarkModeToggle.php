<?php

namespace SymfonyDDD\CoreBundle\Tests\Unit\ValueObject\Mock;

use SymfonyDDD\CoreBundle\Domain\ValueObject\ToggleValue;

readonly class DarkModeToggle extends ToggleValue
{
    public static function enabled(): self
    {
        return new self(true);
    }

    public static function disabled(): self
    {
        return new self(false);
    }

    public static function fromBoolean(bool $value): self
    {
        return new self($value);
    }
}
