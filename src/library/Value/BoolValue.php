<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\library\Value;

use SymfonyDDD\ToolkitBundle\library\Value;

abstract readonly class BoolValue implements Value
{
    protected function __construct(
        public bool $value
    ) {
        $this->validate($value);
    }

    protected function validate(bool $value): void
    {
        // Override this method in your concrete value object
    }

    public function eq(BoolValue $other): bool
    {
        return $this->value === $other->value && $this::class === $other::class;
    }

    public function neq(BoolValue $other): bool
    {
        return !$this->eq($other);
    }

    public function and(BoolValue $other): bool
    {
        return $this->value && $other->value;
    }

    public function or(BoolValue $other): bool
    {
        return $this->value || $other->value;
    }

    public function not(): bool
    {
        return !$this->value;
    }
}
