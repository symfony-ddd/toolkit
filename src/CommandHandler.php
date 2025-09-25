<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class CommandHandler
{
}
