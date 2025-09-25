<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use SymfonyDDD\ToolkitBundle\DependencyInjection\Configuration;

class ConfigurationFilesTest extends TestCase
{
    private Configuration $configuration;
    private Processor $processor;
    private string $fixturesPath;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
        $this->fixturesPath = __DIR__ . '/../../Fixtures/config';
    }

    public function testDefaultConfigurationFile(): void
    {
        $configFile = $this->fixturesPath . '/domain.yaml';
        $this->assertFileExists($configFile);

        $yamlContent = Yaml::parseFile($configFile);
        $this->assertArrayHasKey('domain', $yamlContent);

        $config = $this->processor->processConfiguration($this->configuration, [$yamlContent['domain']]);

        // Test default values from file
        $this->assertEquals('messenger.bus.commands', $config['buses']['command_bus']['service_id']);
        $this->assertEquals([], $config['buses']['command_bus']['middleware']);
        $this->assertEquals('messenger.bus.events', $config['buses']['event_bus']['service_id']);
        $this->assertEquals([], $config['buses']['event_bus']['middleware']);
    }

    public function testCustomBusesConfigurationFile(): void
    {
        $configFile = $this->fixturesPath . '/custom_buses.yaml';
        $this->assertFileExists($configFile);

        $yamlContent = Yaml::parseFile($configFile);
        $config = $this->processor->processConfiguration($this->configuration, [$yamlContent['domain']]);

        // Test custom command bus
        $this->assertEquals('app.custom_command_bus', $config['buses']['command_bus']['service_id']);
        $this->assertEquals(
            ['app.validation_middleware', 'app.logging_middleware'],
            $config['buses']['command_bus']['middleware']
        );

        // Test custom event bus
        $this->assertEquals('app.custom_event_bus', $config['buses']['event_bus']['service_id']);
        $this->assertEquals(
            ['app.audit_middleware', 'app.event_logging_middleware'],
            $config['buses']['event_bus']['middleware']
        );
    }

    public function testPartialConfigurationFile(): void
    {
        $configFile = $this->fixturesPath . '/partial_config.yaml';
        $this->assertFileExists($configFile);

        $yamlContent = Yaml::parseFile($configFile);
        $config = $this->processor->processConfiguration($this->configuration, [$yamlContent['domain']]);

        // Test partial configuration - command bus customized, event bus defaults
        $this->assertEquals('app.custom_command_bus', $config['buses']['command_bus']['service_id']);
        $this->assertEquals([], $config['buses']['command_bus']['middleware']);

        // Event bus should use defaults
        $this->assertEquals('messenger.bus.events', $config['buses']['event_bus']['service_id']);
        $this->assertEquals([], $config['buses']['event_bus']['middleware']);
    }

    public function testConfigurationFileStructure(): void
    {
        $configFile = $this->fixturesPath . '/domain.yaml';
        $yamlContent = Yaml::parseFile($configFile);

        // Test YAML structure
        $this->assertIsArray($yamlContent);
        $this->assertArrayHasKey('domain', $yamlContent);
        $this->assertArrayHasKey('buses', $yamlContent['domain']);
        $this->assertArrayHasKey('command_bus', $yamlContent['domain']['buses']);
        $this->assertArrayHasKey('event_bus', $yamlContent['domain']['buses']);

        // Test command bus structure
        $commandBus = $yamlContent['domain']['buses']['command_bus'];
        $this->assertArrayHasKey('service_id', $commandBus);
        $this->assertArrayHasKey('middleware', $commandBus);

        // Test event bus structure
        $eventBus = $yamlContent['domain']['buses']['event_bus'];
        $this->assertArrayHasKey('service_id', $eventBus);
        $this->assertArrayHasKey('middleware', $eventBus);
    }

    public function testConfigurationFileComments(): void
    {
        $configFile = $this->fixturesPath . '/domain.yaml';
        $fileContent = file_get_contents($configFile);

        // Test that comments are present for documentation
        $this->assertStringContainsString('# Example configuration', $fileContent);
        $this->assertStringContainsString('# Use default command bus', $fileContent);
        $this->assertStringContainsString('# Or override with custom bus', $fileContent);
        $this->assertStringContainsString('service_id of the command bus', $fileContent);
    }

    public function testAllConfigurationFilesAreValid(): void
    {
        $configFiles = [
            'domain.yaml',
            'custom_buses.yaml', 
            'partial_config.yaml'
        ];

        foreach ($configFiles as $filename) {
            $configFile = $this->fixturesPath . '/' . $filename;
            $this->assertFileExists($configFile, "Configuration file {$filename} should exist");

            $yamlContent = Yaml::parseFile($configFile);
            $this->assertIsArray($yamlContent, "Configuration file {$filename} should contain valid YAML");
            $this->assertArrayHasKey('domain', $yamlContent, "Configuration file {$filename} should have 'domain' key");

            // Test that configuration is processable
            $config = $this->processor->processConfiguration($this->configuration, [$yamlContent['domain']]);
            $this->assertIsArray($config, "Configuration from {$filename} should be processable");
            $this->assertArrayHasKey('buses', $config, "Processed config from {$filename} should have 'buses' key");
        }
    }

    public function testConfigurationFileValues(): void
    {
        $testCases = [
            [
                'file' => 'domain.yaml',
                'expected_command_service' => 'messenger.bus.commands',
                'expected_command_middleware' => [],
                'expected_event_service' => 'messenger.bus.events',
                'expected_event_middleware' => [],
            ],
            [
                'file' => 'custom_buses.yaml',
                'expected_command_service' => 'app.custom_command_bus',
                'expected_command_middleware' => ['app.validation_middleware', 'app.logging_middleware'],
                'expected_event_service' => 'app.custom_event_bus',
                'expected_event_middleware' => ['app.audit_middleware', 'app.event_logging_middleware'],
            ],
            [
                'file' => 'partial_config.yaml',
                'expected_command_service' => 'app.custom_command_bus',
                'expected_command_middleware' => [],
                'expected_event_service' => 'messenger.bus.events',
                'expected_event_middleware' => [],
            ],
        ];

        foreach ($testCases as $testCase) {
            $configFile = $this->fixturesPath . '/' . $testCase['file'];
            $yamlContent = Yaml::parseFile($configFile);
            $config = $this->processor->processConfiguration($this->configuration, [$yamlContent['domain']]);

            $this->assertEquals(
                $testCase['expected_command_service'],
                $config['buses']['command_bus']['service_id'],
                "Command service ID mismatch in {$testCase['file']}"
            );

            $this->assertEquals(
                $testCase['expected_command_middleware'],
                $config['buses']['command_bus']['middleware'],
                "Command middleware mismatch in {$testCase['file']}"
            );

            $this->assertEquals(
                $testCase['expected_event_service'],
                $config['buses']['event_bus']['service_id'],
                "Event service ID mismatch in {$testCase['file']}"
            );

            $this->assertEquals(
                $testCase['expected_event_middleware'],
                $config['buses']['event_bus']['middleware'],
                "Event middleware mismatch in {$testCase['file']}"
            );
        }
    }
}
