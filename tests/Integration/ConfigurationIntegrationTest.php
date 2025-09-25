<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use SymfonyDDD\ToolkitBundle\DependencyInjection\ToolkitExtension;

class ConfigurationIntegrationTest extends TestCase
{
    private string $fixturesPath;

    protected function setUp(): void
    {
        $this->fixturesPath = __DIR__ . '/../Fixtures/config';
    }

    public function testDefaultConfigurationIntegration(): void
    {
        $container = new ContainerBuilder();
        $extension = new ToolkitExtension();

        // Load configuration from file
        $loader = new YamlFileLoader($container, new FileLocator($this->fixturesPath));
        $loader->load('default.yaml');

        // Process with extension
        $configs = $container->getExtensionConfig('symfony_ddd_toolkit');
        $extension->load($configs, $container);

        // Verify services are registered
        $this->assertTrue($container->hasAlias('symfony_ddd_toolkit.command_bus'));
        $this->assertTrue($container->hasAlias('symfony_ddd_toolkit.event_bus'));

        // Verify default buses are created
        $this->assertTrue($container->hasDefinition('messenger.bus.commands'));
        $this->assertTrue($container->hasDefinition('messenger.bus.events'));
    }

    public function testCustomBusesConfigurationIntegration(): void
    {
        $container = new ContainerBuilder();
        $extension = new ToolkitExtension();

        // Load configuration from file
        $loader = new YamlFileLoader($container, new FileLocator($this->fixturesPath));
        $loader->load('custom_buses.yaml');

        // Process with extension
        $configs = $container->getExtensionConfig('symfony_ddd_toolkit');
        $extension->load($configs, $container);

        // Verify aliases point to custom services
        $commandBusAlias = $container->getAlias('symfony_ddd_toolkit.command_bus');
        $this->assertEquals('app.custom_command_bus', (string) $commandBusAlias);

        $eventBusAlias = $container->getAlias('symfony_ddd_toolkit.event_bus');
        $this->assertEquals('app.custom_event_bus', (string) $eventBusAlias);

        // Verify custom buses are created (since they don't exist)
        $this->assertTrue($container->hasDefinition('app.custom_command_bus'));
        $this->assertTrue($container->hasDefinition('app.custom_event_bus'));
    }

    public function testPartialConfigurationIntegration(): void
    {
        $container = new ContainerBuilder();
        $extension = new ToolkitExtension();

        // Load configuration from file
        $loader = new YamlFileLoader($container, new FileLocator($this->fixturesPath));
        $loader->load('partial_config.yaml');

        // Process with extension
        $configs = $container->getExtensionConfig('domain');
        $extension->load($configs, $container);

        // Verify mixed configuration
        $commandBusAlias = $container->getAlias('symfony_ddd_toolkit.command_bus');
        $this->assertEquals('app.custom_command_bus', (string) $commandBusAlias);

        $eventBusAlias = $container->getAlias('symfony_ddd_toolkit.event_bus');
        $this->assertEquals('messenger.bus.events', (string) $eventBusAlias);

        // Verify appropriate buses are created
        $this->assertTrue($container->hasDefinition('app.custom_command_bus'));
        $this->assertTrue($container->hasDefinition('messenger.bus.events'));
    }

}
