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
        $configFile = $this->fixturesPath . '/default.yaml';
        $this->assertFileExists($configFile);

        $yamlContent = Yaml::parseFile($configFile);
        $this->assertArrayHasKey('domain', $yamlContent);

        $config = $this->processor->processConfiguration($this->configuration, [$yamlContent['domain']]);

        // Test default values from file
        $this->assertEquals('symfony_ddd_toolkit.command_bus', $config['buses']['command_bus']);
        $this->assertEquals('symfony_ddd_toolkit.event_bus', $config['buses']['event_bus']);
    }

    public function testCustomBusesConfigurationFile(): void
    {
        $configFile = $this->fixturesPath . '/custom_buses.yaml';
        $this->assertFileExists($configFile);

        $yamlContent = Yaml::parseFile($configFile);
        $config = $this->processor->processConfiguration($this->configuration, [$yamlContent['domain']]);

        // Test custom command bus
        $this->assertEquals('app.custom_command_bus', $config['buses']['command_bus']);

        // Test custom event bus
        $this->assertEquals('app.custom_event_bus', $config['buses']['event_bus']);
    }

    public function testPartialConfigurationFile(): void
    {
        $configFile = $this->fixturesPath . '/partial_config.yaml';
        $this->assertFileExists($configFile);

        $yamlContent = Yaml::parseFile($configFile);
        $config = $this->processor->processConfiguration($this->configuration, [$yamlContent['domain']]);

        // Test partial configuration - command bus customized, event bus defaults
        $this->assertEquals('app.custom_command_bus', $config['buses']['command_bus']);

        // Event bus should use defaults
        $this->assertEquals('symfony_ddd_toolkit.bus.events', $config['buses']['event_bus']);
    }

    public function testConfigurationFileStructure(): void
    {
        $configFile = $this->fixturesPath . '/default.yaml';
        $yamlContent = Yaml::parseFile($configFile);

        // Test YAML structure
        $this->assertIsArray($yamlContent);
        $this->assertArrayHasKey('domain', $yamlContent);
        $this->assertArrayHasKey('buses', $yamlContent['domain']);
        $this->assertArrayHasKey('command_bus', $yamlContent['domain']['buses']);
        $this->assertArrayHasKey('event_bus', $yamlContent['domain']['buses']);

        // Test command bus structure (now scalar)
        $this->assertIsString($yamlContent['symfony_ddd_toolkit']['buses']['command_bus']);

        // Test event bus structure (now scalar)
        $this->assertIsString($yamlContent['symfony_ddd_toolkit']['buses']['event_bus']);
    }

    public function testConfigurationFileComments(): void
    {
        $configFile = $this->fixturesPath . '/default.yaml';
        $fileContent = file_get_contents($configFile);

        // Test that comments are present for documentation
        $this->assertStringContainsString('# Example configuration', $fileContent);
    }

    public function testAllConfigurationFilesAreValid(): void
    {
        $configFiles = [
            'default.yaml',
            'custom_buses.yaml', 
            'partial_config.yaml'
        ];

        foreach ($configFiles as $filename) {
            $configFile = $this->fixturesPath . '/' . $filename;
            $this->assertFileExists($configFile, "Configuration file {$filename} should exist");

            $yamlContent = Yaml::parseFile($configFile);
            $this->assertIsArray($yamlContent, "Configuration file {$filename} should contain valid YAML");
            $this->assertArrayHasKey('symfony_ddd_toolkit', $yamlContent, "Configuration file {$filename} should have 'symfony_ddd_toolkit' key");

            // Test that configuration is processable
            $config = $this->processor->processConfiguration($this->configuration, [$yamlContent['symfony_ddd_toolkit']]);
            $this->assertIsArray($config, "Configuration from {$filename} should be processable");
            $this->assertArrayHasKey('buses', $config, "Processed config from {$filename} should have 'buses' key");
        }
    }

    public function testConfigurationFileValues(): void
    {
        $testCases = [
            [
                'file' => 'default.yaml',
                'expected_command_service' => 'symfony_ddd_toolkit.command_bus',
                'expected_event_service' => 'symfony_ddd_toolkit.event_bus',
            ],
            [
                'file' => 'custom_buses.yaml',
                'expected_command_service' => 'app.custom_command_bus',
                'expected_event_service' => 'app.custom_event_bus',
            ],
            [
                'file' => 'partial_config.yaml',
                'expected_command_service' => 'app.custom_command_bus',
                'expected_event_service' => 'symfony_ddd_toolkit.bus.events',
            ],
        ];

        foreach ($testCases as $testCase) {
            $configFile = $this->fixturesPath . '/' . $testCase['file'];
            $yamlContent = Yaml::parseFile($configFile);
            $config = $this->processor->processConfiguration($this->configuration, [$yamlContent['symfony_ddd_toolkit']]);

            $this->assertEquals(
                $testCase['expected_command_service'],
                $config['buses']['command_bus'],
                "Command service ID mismatch in {$testCase['file']}"
            );

            $this->assertEquals(
                $testCase['expected_event_service'],
                $config['buses']['event_bus'],
                "Event service ID mismatch in {$testCase['file']}"
            );
        }
    }
}
