<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\library\Value;

use SymfonyDDD\ToolkitBundle\library\Value;

abstract readonly class StringValue implements Value
{
    protected function __construct(
        public string $value
    ) {
        $this->validate($value);
    }

    protected function validate(string $value): void
    {
        // Override this method in your concrete value object
    }

    public function eq(StringValue $other): bool
    {
        return $this->value === $other->value && $this::class === $other::class;
    }

    public function neq(StringValue $other): bool
    {
        return !$this->eq($other);
    }

    public function gt(StringValue $other): bool
    {
        return $this->value > $other->value;
    }

    public function lt(StringValue $other): bool
    {
        return $this->value < $other->value;
    }

    public function gte(StringValue $other): bool
    {
        return $this->value >= $other->value;
    }

    public function lte(StringValue $other): bool
    {
        return $this->value <= $other->value;
    }

    public function length(): int
    {
        return strlen($this->value);
    }

    public function isEmpty(): bool
    {
        return $this->value === '';
    }

    public function contains(string $needle): bool
    {
        return str_contains($this->value, $needle);
    }

    public function startsWith(string $prefix): bool
    {
        return str_starts_with($this->value, $prefix);
    }

    public function endsWith(string $suffix): bool
    {
        return str_ends_with($this->value, $suffix);
    }

    public function toUpper(): string
    {
        return strtoupper($this->value);
    }

    public function toLower(): string
    {
        return strtolower($this->value);
    }
}
