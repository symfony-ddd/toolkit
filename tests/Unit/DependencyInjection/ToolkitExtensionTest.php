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

        // Create config directory structure for file loading
        if (!is_dir(__DIR__ . '/../../../config')) {
            mkdir(__DIR__ . '/../../../config', 0755, true);
        }

        // Create empty services.php file for testing
        if (!file_exists(__DIR__ . '/../../../config/services.php')) {
            file_put_contents(__DIR__ . '/../../../config/services.php', '<?php return [];');
        }
    }

    public function testExtensionAlias(): void
    {
        $this->assertEquals('domain', $this->extension->getAlias());
    }

    public function testDefaultBusConfiguration(): void
    {
        $config = [];

        $this->extension->load([$config], $this->container);

        // Check that aliases are created
        $this->assertTrue($this->container->hasAlias('symfony_ddd_toolkit.command_bus'));
        $this->assertTrue($this->container->hasAlias('symfony_ddd_toolkit.event_bus'));

        // Check that default buses are created
        $this->assertTrue($this->container->hasDefinition('messenger.bus.commands'));
        $this->assertTrue($this->container->hasDefinition('messenger.bus.events'));

        // Check bus definitions
        $commandBusDefinition = $this->container->getDefinition('messenger.bus.commands');
        $this->assertEquals(MessageBus::class, $commandBusDefinition->getClass());
        $this->assertTrue($commandBusDefinition->hasTag('messenger.bus'));

        $eventBusDefinition = $this->container->getDefinition('messenger.bus.events');
        $this->assertEquals(MessageBus::class, $eventBusDefinition->getClass());
        $this->assertTrue($eventBusDefinition->hasTag('messenger.bus'));

        // Check aliases point to correct services
        $commandBusAlias = $this->container->getAlias('symfony_ddd_toolkit.command_bus');
        $this->assertEquals('messenger.bus.commands', (string) $commandBusAlias);

        $eventBusAlias = $this->container->getAlias('symfony_ddd_toolkit.event_bus');
        $this->assertEquals('messenger.bus.events', (string) $eventBusAlias);
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
                'command_bus' => [
                    'service_id' => 'app.custom_command_bus'
                ],
                'event_bus' => [
                    'service_id' => 'app.custom_event_bus'
                ]
            ]
        ];

        $this->extension->load([$config], $this->container);

        // Check aliases point to custom services
        $commandBusAlias = $this->container->getAlias('symfony_ddd_toolkit.command_bus');
        $this->assertEquals('app.custom_command_bus', (string) $commandBusAlias);

        $eventBusAlias = $this->container->getAlias('symfony_ddd_toolkit.event_bus');
        $this->assertEquals('app.custom_event_bus', (string) $eventBusAlias);

        // Default buses should not be created
        $this->assertFalse($this->container->hasDefinition('messenger.bus.commands'));
        $this->assertFalse($this->container->hasDefinition('messenger.bus.events'));
    }

    public function testMiddlewareConfiguration(): void
    {
        $config = [
            'buses' => [
                'command_bus' => [
                    'middleware' => ['app.validation_middleware', 'app.logging_middleware']
                ],
                'event_bus' => [
                    'middleware' => ['app.audit_middleware']
                ]
            ]
        ];

        $this->extension->load([$config], $this->container);

        $commandBusDefinition = $this->container->getDefinition('messenger.bus.commands');
        $eventBusDefinition = $this->container->getDefinition('messenger.bus.events');

        // Check that middleware arguments include custom middleware
        $commandBusArguments = $commandBusDefinition->getArguments();
        $eventBusArguments = $eventBusDefinition->getArguments();

        $this->assertIsArray($commandBusArguments[0]);
        $this->assertIsArray($eventBusArguments[0]);

        // Middleware should be prepended to default middleware
        $this->assertCount(4, $commandBusArguments[0]); // 2 custom + 2 default
        $this->assertCount(3, $eventBusArguments[0]); // 1 custom + 2 default
    }

    public function testDefaultMiddlewareCreation(): void
    {
        $config = [];

        $this->extension->load([$config], $this->container);

        // Default middleware should be created
        $this->assertTrue($this->container->hasDefinition('messenger.middleware.send_message'));
        $this->assertTrue($this->container->hasDefinition('messenger.middleware.handle_message'));
    }

    public function testMixedConfiguration(): void
    {
        // Pre-define only command bus, event bus should use default
        $customCommandBus = new Definition(MessageBus::class);
        $this->container->setDefinition('app.custom_command_bus', $customCommandBus);

        $config = [
            'buses' => [
                'command_bus' => [
                    'service_id' => 'app.custom_command_bus',
                    'middleware' => ['app.validation_middleware']
                ]
                // event_bus uses defaults
            ]
        ];

        $this->extension->load([$config], $this->container);

        // Command bus should use custom service
        $commandBusAlias = $this->container->getAlias('symfony_ddd_toolkit.command_bus');
        $this->assertEquals('app.custom_command_bus', (string) $commandBusAlias);

        // Event bus should use default
        $eventBusAlias = $this->container->getAlias('symfony_ddd_toolkit.event_bus');
        $this->assertEquals('messenger.bus.events', (string) $eventBusAlias);

        // Default event bus should be created
        $this->assertTrue($this->container->hasDefinition('messenger.bus.events'));
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (file_exists(__DIR__ . '/../../../config/services.php')) {
            unlink(__DIR__ . '/../../../config/services.php');
        }
    }
}
