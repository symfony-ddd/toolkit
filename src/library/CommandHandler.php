<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\library;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class CommandHandler
{
    public function __construct(
        public bool $manualEventRelease = false,
    ) {
    }
}
