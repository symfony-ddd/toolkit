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

        // Create required directory and file
        if (!is_dir(__DIR__ . '/../../../config')) {
            mkdir(__DIR__ . '/../../../config', 0755, true);
        }
        if (!file_exists(__DIR__ . '/../../../config/services.php')) {
            file_put_contents(__DIR__ . '/../../../config/services.php', '<?php return [];');
        }
    }

    public function testCompleteWorkflow(): void
    {
        $inputConfig = [
            'buses' => [
                'command_bus' => [
                    'service_id' => 'app.command_bus',
                    'middleware' => ['app.validation_middleware']
                ],
                'event_bus' => [
                    'service_id' => 'messenger.bus.events',
                    'middleware' => ['app.audit_middleware']
                ]
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

    public function testBusCreationWithProperMiddlewareOrder(): void
    {
        $config = [
            'buses' => [
                'command_bus' => [
                    'middleware' => ['middleware.first', 'middleware.second']
                ]
            ]
        ];

        $this->extension->load([$config], $this->container);

        $busDefinition = $this->container->getDefinition('messenger.bus.commands');
        $middleware = $busDefinition->getArguments()[0];

        // Middleware should be: custom middleware first, then default middleware
        $this->assertInstanceOf(Reference::class, $middleware[0]);
        $this->assertEquals('middleware.first', (string) $middleware[0]);

        $this->assertInstanceOf(Reference::class, $middleware[1]);
        $this->assertEquals('middleware.second', (string) $middleware[1]);

        $this->assertInstanceOf(Reference::class, $middleware[2]);
        $this->assertEquals('messenger.middleware.send_message', (string) $middleware[2]);

        $this->assertInstanceOf(Reference::class, $middleware[3]);
        $this->assertEquals('messenger.middleware.handle_message', (string) $middleware[3]);
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

    public function testDefaultMiddlewareCreationWhenMissing(): void
    {
        $config = [];

        // Ensure middleware doesn't exist
        $this->assertFalse($this->container->hasDefinition('messenger.middleware.send_message'));
        $this->assertFalse($this->container->hasDefinition('messenger.middleware.handle_message'));

        $this->extension->load([$config], $this->container);

        // Middleware should be created
        $this->assertTrue($this->container->hasDefinition('messenger.middleware.send_message'));
        $this->assertTrue($this->container->hasDefinition('messenger.middleware.handle_message'));
    }

    public function testExistingMiddlewareNotOverridden(): void
    {
        // Pre-define middleware
        $existingMiddleware = new Definition('CustomSendMiddleware');
        $this->container->setDefinition('messenger.middleware.send_message', $existingMiddleware);

        $config = [];
        $this->extension->load([$config], $this->container);

        // Should not be overridden
        $middlewareDefinition = $this->container->getDefinition('messenger.middleware.send_message');
        $this->assertEquals('CustomSendMiddleware', $middlewareDefinition->getClass());
    }

    protected function tearDown(): void
    {
        if (file_exists(__DIR__ . '/../../../config/services.php')) {
            unlink(__DIR__ . '/../../../config/services.php');
        }
    }
}
