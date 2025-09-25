<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Messenger\MessageBus;
use SymfonyDDD\ToolkitBundle\DependencyInjection\ToolkitExtension;

class ToolkitExtensionTest extends TestCase
{
    private ToolkitExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new ToolkitExtension();
        $this->container = new ContainerBuilder();
    }

    public function testExtensionAlias(): void
    {
        $this->assertEquals('symfony_ddd_toolkit', $this->extension->getAlias());
    }

    public function testDefaultBusConfiguration(): void
    {
        $config = [];

        $this->extension->load([$config], $this->container);

        // Check that aliases are created
        $this->assertTrue($this->container->hasAlias('symfony_ddd_toolkit.command_bus'));
        $this->assertTrue($this->container->hasAlias('symfony_ddd_toolkit.event_bus'));

        // Check that default buses are created
        $this->assertTrue($this->container->hasDefinition('symfony_ddd_toolkit.bus.commands'));
        $this->assertTrue($this->container->hasDefinition('symfony_ddd_toolkit.bus.events'));

        // Check bus definitions
        $commandBusDefinition = $this->container->getDefinition('symfony_ddd_toolkit.bus.commands');
        $this->assertEquals(MessageBus::class, $commandBusDefinition->getClass());
        $this->assertTrue($commandBusDefinition->hasTag('messenger.bus'));

        $eventBusDefinition = $this->container->getDefinition('symfony_ddd_toolkit.bus.events');
        $this->assertEquals(MessageBus::class, $eventBusDefinition->getClass());
        $this->assertTrue($eventBusDefinition->hasTag('messenger.bus'));

        // Check aliases point to correct services
        $commandBusAlias = $this->container->getAlias('symfony_ddd_toolkit.command_bus');
        $this->assertEquals('symfony_ddd_toolkit.bus.commands', (string) $commandBusAlias);

        $eventBusAlias = $this->container->getAlias('symfony_ddd_toolkit.event_bus');
        $this->assertEquals('symfony_ddd_toolkit.bus.events', (string) $eventBusAlias);
    }

    public function testCustomBusConfiguration(): void
    {
        // Pre-define custom buses
        $customCommandBus = new Definition(MessageBus::class);
        $customEventBus = new Definition(MessageBus::class);

        $this->container->setDefinition('app.custom_command_bus', $customCommandBus);
        $this->container->setDefinition('app.custom_event_bus', $customEventBus);

        $config = [
            'buses' => [
                'command_bus' => 'app.custom_command_bus',
                'event_bus' => 'app.custom_event_bus',
            ]
        ];

        $this->extension->load([$config], $this->container);

        // Check aliases point to custom services
        $commandBusAlias = $this->container->getAlias('symfony_ddd_toolkit.command_bus');
        $this->assertEquals('app.custom_command_bus', (string) $commandBusAlias);

        $eventBusAlias = $this->container->getAlias('symfony_ddd_toolkit.event_bus');
        $this->assertEquals('app.custom_event_bus', (string) $eventBusAlias);

        // Default buses should not be created
        $this->assertFalse($this->container->hasDefinition('symfony_ddd_toolkit.bus.commands'));
        $this->assertFalse($this->container->hasDefinition('symfony_ddd_toolkit.bus.events'));
    }



    public function testMixedConfiguration(): void
    {
        // Pre-define only command bus, event bus should use default
        $customCommandBus = new Definition(MessageBus::class);
        $this->container->setDefinition('app.custom_command_bus', $customCommandBus);

        $config = [
            'buses' => [
                'command_bus' => 'app.custom_command_bus'
            ]
        ];

        $this->extension->load([$config], $this->container);

        // Command bus should use custom service
        $commandBusAlias = $this->container->getAlias('symfony_ddd_toolkit.command_bus');
        $this->assertEquals('app.custom_command_bus', (string) $commandBusAlias);

        // Event bus should use default
        $eventBusAlias = $this->container->getAlias('symfony_ddd_toolkit.event_bus');
        $this->assertEquals('symfony_ddd_toolkit.bus.events', (string) $eventBusAlias);

        // Default event bus should be created
        $this->assertTrue($this->container->hasDefinition('symfony_ddd_toolkit.bus.events'));
    }
}
