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

        $this->assertEquals('messenger.bus.commands', $config['buses']['command_bus']['service_id']);
        $this->assertEquals([], $config['buses']['command_bus']['middleware']);

        $this->assertEquals('messenger.bus.events', $config['buses']['event_bus']['service_id']);
        $this->assertEquals([], $config['buses']['event_bus']['middleware']);
    }

    public function testCustomConfiguration(): void
    {
        $inputConfig = [
            'buses' => [
                'command_bus' => [
                    'service_id' => 'app.custom_command_bus',
                    'middleware' => ['app.validation_middleware', 'app.logging_middleware']
                ],
                'event_bus' => [
                    'service_id' => 'app.custom_event_bus',
                    'middleware' => ['app.audit_middleware']
                ]
            ]
        ];

        $config = $this->processor->processConfiguration($this->configuration, [$inputConfig]);

        $this->assertEquals('app.custom_command_bus', $config['buses']['command_bus']['service_id']);
        $this->assertEquals(
            ['app.validation_middleware', 'app.logging_middleware'],
            $config['buses']['command_bus']['middleware']
        );

        $this->assertEquals('app.custom_event_bus', $config['buses']['event_bus']['service_id']);
        $this->assertEquals(['app.audit_middleware'], $config['buses']['event_bus']['middleware']);
    }

    public function testPartialConfiguration(): void
    {
        $inputConfig = [
            'buses' => [
                'command_bus' => [
                    'service_id' => 'app.custom_command_bus'
                ]
            ]
        ];

        $config = $this->processor->processConfiguration($this->configuration, [$inputConfig]);

        // Custom command bus
        $this->assertEquals('app.custom_command_bus', $config['buses']['command_bus']['service_id']);
        $this->assertEquals([], $config['buses']['command_bus']['middleware']);

        // Default event bus
        $this->assertEquals('messenger.bus.events', $config['buses']['event_bus']['service_id']);
        $this->assertEquals([], $config['buses']['event_bus']['middleware']);
    }

    public function testEmptyConfiguration(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [[]]);

        $this->assertArrayHasKey('buses', $config);
        $this->assertEquals('messenger.bus.commands', $config['buses']['command_bus']['service_id']);
        $this->assertEquals('messenger.bus.events', $config['buses']['event_bus']['service_id']);
    }

    public function testTreeBuilderRootName(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();
        $this->assertEquals('symfony_ddd_toolkit', $treeBuilder->getRootNode()->getName());
    }
}
