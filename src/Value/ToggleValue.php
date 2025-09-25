<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Value;

use SymfonyDDD\ToolkitBundle\Value;

abstract readonly class ToggleValue implements Value
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

    public function eq(ToggleValue $other): bool
    {
        return $this->value === $other->value && $this::class === $other::class;
    }

    public function neq(ToggleValue $other): bool
    {
        return !$this->eq($other);
    }

    public function isEnabled(): bool
    {
        return $this->value === true;
    }

    public function isDisabled(): bool
    {
        return $this->value === false;
    }

    public function toggle(): static
    {
        return new static(!$this->value);
    }

    public function and(ToggleValue $other): bool
    {
        return $this->value && $other->value;
    }

    public function or(ToggleValue $other): bool
    {
        return $this->value || $other->value;
    }

    public function not(): bool
    {
        return !$this->value;
    }
}
