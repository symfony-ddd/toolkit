<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyDDD\ToolkitBundle\ToolkitBundle;

class ToolkitBundleTest extends KernelTestCase
{
    public function testBundleIsLoaded(): void
    {
        self::bootKernel();

        $bundles = self::$kernel->getBundles();
        $this->assertInstanceOf(ToolkitBundle::class, $bundles['ToolkitBundle'] ?? null);
    }
}
