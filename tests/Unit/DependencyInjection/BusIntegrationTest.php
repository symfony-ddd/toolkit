<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBus;
use SymfonyDDD\ToolkitBundle\DependencyInjection\Configuration;
use SymfonyDDD\ToolkitBundle\DependencyInjection\ToolkitExtension;

class BusIntegrationTest extends TestCase
{
    private ToolkitExtension $extension;
    private Configuration $configuration;
    private Processor $processor;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new ToolkitExtension();
        $this->configuration = new Configuration();
        $this->processor = new Processor();
        $this->container = new ContainerBuilder();
    }

    public function testCompleteWorkflow(): void
    {
        $inputConfig = [
            'buses' => [
                'command_bus' => 'app.custom_command_bus',
                'event_bus' => 'app.custom_event_bus'
            ]
        ];

        // Process configuration
        $processedConfig = $this->processor->processConfiguration($this->configuration, [$inputConfig]);

        // Pre-define the custom command bus
        $customCommandBus = new Definition(MessageBus::class);
        $this->container->setDefinition('app.command_bus', $customCommandBus);

        // Load configuration
        $this->extension->load([$inputConfig], $this->container);

        // Verify aliases exist
        $this->assertTrue($this->container->hasAlias('symfony_ddd_toolkit.command_bus'));
        $this->assertTrue($this->container->hasAlias('symfony_ddd_toolkit.event_bus'));

        // Verify command bus uses existing service
        $commandBusAlias = $this->container->getAlias('symfony_ddd_toolkit.command_bus');
        $this->assertEquals('app.command_bus', (string) $commandBusAlias);

        // Verify event bus is created with middleware
        $eventBusAlias = $this->container->getAlias('symfony_ddd_toolkit.event_bus');
        $this->assertEquals('messenger.bus.events', (string) $eventBusAlias);

        $eventBusDefinition = $this->container->getDefinition('messenger.bus.events');
        $this->assertEquals(MessageBus::class, $eventBusDefinition->getClass());

        // Check middleware configuration
        $middleware = $eventBusDefinition->getArguments()[0];
        $this->assertIsArray($middleware);
        $this->assertCount(3, $middleware); // 1 custom + 2 default
    }

    public function testBusTagging(): void
    {
        $config = [];

        $this->extension->load([$config], $this->container);

        $commandBusDefinition = $this->container->getDefinition('messenger.bus.commands');
        $eventBusDefinition = $this->container->getDefinition('messenger.bus.events');

        $this->assertTrue($commandBusDefinition->hasTag('messenger.bus'));
        $this->assertTrue($eventBusDefinition->hasTag('messenger.bus'));
    }
}
