<?php
declare(strict_types=1);

namespace Integration\Fixture\Domain\Value;

use SymfonyDDD\ToolkitBundle\library\Value\BoolValue;

readonly class Placement extends BoolValue
{
    public static function uncommited(): self
    {
        return new self(false);
    }

    public static function commited(): self
    {
        return new self(true);
    }

    public function isCommited(): bool
    {
        return $this->value === true;
    }
}