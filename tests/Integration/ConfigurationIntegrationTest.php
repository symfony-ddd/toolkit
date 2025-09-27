<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use SymfonyDDD\ToolkitBundle\DependencyInjection\ToolkitExtension;

class ConfigurationIntegrationTest extends KernelTestCase
{
    private string $fixturesPath;

    protected function setUp(): void
    {
        $this->fixturesPath = __DIR__ . '/../Fixture/config';
    }

    public function testDefaultConfigurationIntegration(): void
    {
        $container = new ContainerBuilder();
        $extension = new ToolkitExtension();
        $container->registerExtension($extension); // 👈 important
        $container->loadFromExtension($extension->getAlias());

        // Load configuration from file
        $loader = new YamlFileLoader($container, new FileLocator($this->fixturesPath));
        $loader->load('default.yaml');

        // Process with extension
        $configs = $container->getExtensionConfig('ddd_toolkit');
        $extension->load($configs, $container);

        // Verify services are registered
        $this->assertTrue($container->hasAlias('ddd_toolkit.command_bus'));
        $this->assertTrue($container->hasAlias('ddd_toolkit.event_bus'));

        // Verify default buses are created
        $this->assertTrue($container->hasDefinition('ddd_toolkit.bus.events'));
        $this->assertTrue($container->hasDefinition('ddd_toolkit.bus.events'));
    }

    public function testCustomBusesConfigurationIntegration(): void
    {
        $container = new ContainerBuilder();
        $extension = new ToolkitExtension();
        $container->registerExtension($extension); // 👈 important
        $container->loadFromExtension($extension->getAlias());

        // Load configuration from file
        $loader = new YamlFileLoader($container, new FileLocator($this->fixturesPath));
        $loader->load('custom_buses.yaml');

        // Process with extension
        $configs = $container->getExtensionConfig('ddd_toolkit');
        $extension->load($configs, $container);

        // Verify aliases point to custom services
        $commandBusAlias = $container->getAlias('ddd_toolkit.command_bus');
        $this->assertEquals('app.custom_command_bus', (string) $commandBusAlias);

        $eventBusAlias = $container->getAlias('ddd_toolkit.event_bus');
        $this->assertEquals('app.custom_event_bus', (string) $eventBusAlias);

        // Verify custom buses are created (since they don't exist)
        $this->assertTrue($container->hasDefinition('app.custom_command_bus'));
        $this->assertTrue($container->hasDefinition('app.custom_event_bus'));
    }

    public function testPartialConfigurationIntegration(): void
    {
        $container = new ContainerBuilder();
        $extension = new ToolkitExtension();
        $container->registerExtension($extension); // 👈 important
        $container->loadFromExtension($extension->getAlias());

        // Load configuration from file
        $loader = new YamlFileLoader($container, new FileLocator($this->fixturesPath));
        $loader->load('partial_config.yaml');

        // Process with extension
        $configs = $container->getExtensionConfig('ddd_toolkit');
        $extension->load($configs, $container);

        // Verify mixed configuration
        $commandBusAlias = $container->getAlias('ddd_toolkit.command_bus');
        $this->assertEquals('app.custom_command_bus', (string) $commandBusAlias);

        $eventBusAlias = $container->getAlias('ddd_toolkit.event_bus');
        $this->assertEquals('ddd_toolkit.bus.events', (string) $eventBusAlias);

        // Verify appropriate buses are created
        $this->assertTrue($container->hasDefinition('app.custom_command_bus'));
        $this->assertTrue($container->hasDefinition('ddd_toolkit.bus.events'));
    }

}
