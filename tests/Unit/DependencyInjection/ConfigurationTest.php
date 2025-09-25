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

        $this->assertEquals('symfony_ddd_toolkit.bus.commands', $config['buses']['command_bus']);
        $this->assertEquals('symfony_ddd_toolkit.bus.events', $config['buses']['event_bus']);
    }

    public function testCustomConfiguration(): void
    {
        $inputConfig = [
            'buses' => [
                'command_bus' => 'app.custom_command_bus',
                'event_bus' => 'app.custom_event_bus'
            ]
        ];

        $config = $this->processor->processConfiguration($this->configuration, [$inputConfig]);

        $this->assertEquals('app.custom_command_bus', $config['buses']['command_bus']);
        $this->assertEquals('app.custom_event_bus', $config['buses']['event_bus']);
    }

    public function testPartialConfiguration(): void
    {
        $inputConfig = [
            'buses' => [
                'command_bus' => 'app.custom_command_bus'
            ]
        ];

        $config = $this->processor->processConfiguration($this->configuration, [$inputConfig]);

        // Custom command bus
        $this->assertEquals('app.custom_command_bus', $config['buses']['command_bus']);

        // Default event bus
        $this->assertEquals('symfony_ddd_toolkit.bus.events', $config['buses']['event_bus']);
    }

    public function testEmptyConfiguration(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [[]]);

        $this->assertArrayHasKey('buses', $config);
        $this->assertEquals('symfony_ddd_toolkit.bus.commands', $config['buses']['command_bus']);
        $this->assertEquals('symfony_ddd_toolkit.bus.events', $config['buses']['event_bus']);
    }

    public function testTreeBuilderRootName(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();
        $this->assertEquals('symfony_ddd_toolkit', $treeBuilder->getRootNode()->getName());
    }
}
