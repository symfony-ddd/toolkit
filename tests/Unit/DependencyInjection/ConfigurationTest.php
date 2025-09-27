<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use SymfonyDDD\ToolkitBundle\DependencyInjection\Configuration;

class ConfigurationTest extends TestCase
{
    private Configuration $configuration;
    private Processor $processor;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testDefaultConfiguration(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, []);

        $this->assertArrayHasKey('buses', $config);
        $this->assertArrayHasKey('command_bus', $config['buses']);
        $this->assertArrayHasKey('event_bus', $config['buses']);

        $this->assertEquals('ddd_toolkit.bus.commands', $config['buses']['command_bus']);
        $this->assertEquals('ddd_toolkit.bus.events', $config['buses']['event_bus']);
    }
}
