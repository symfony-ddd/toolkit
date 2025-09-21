<?php

namespace SymfonyDDD\CoreBundle\Domain\ValueObject;

abstract readonly class IntValue implements Value
{
    // for consistency reasons value object is always created using static factory methods
    protected function __construct(
        public int $value
    ) {
        $this->validate($value);
    }

    protected function validate(int $value): void
    {
        // Override this method in your concrete value object
    }

    public function eq(IntValue $other): bool
    {
        return $this->value === $other->value && $this::class === $other::class;
    }
    
    public function neq(IntValue $other): bool
    {
        return !$this->eq($other);
    }
    
    public function gt(IntValue $other): bool
    {
        return $this->value > $other->value;
    }

    public function lt(IntValue $other): bool
    {
        return $this->value < $other->value;
    }

    public function gte(IntValue $other): bool
    {
        return $this->value >= $other->value;
    }

    public function lte(IntValue $other): bool
    {
        return $this->value <= $other->value;
    }
}
