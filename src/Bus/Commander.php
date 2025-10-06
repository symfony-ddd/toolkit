<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Bus;

final readonly class Commander
{
    public function __construct(
        private CommandBus $inner,
    )
    {
    }
}