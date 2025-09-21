<?php

namespace SymfonyDDD\CoreBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyDDD\CoreBundle\CoreBundle;

class CoreBundleTest extends KernelTestCase
{
    public function testBundleIsLoaded(): void
    {
        self::bootKernel();

        $bundles = self::$kernel->getBundles();
        $this->assertInstanceOf(CoreBundle::class, $bundles['CoreBundle'] ?? null);
    }
}
