<?php
declare(strict_types=1);

namespace SymfonyDDD\ToolkitBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use SymfonyDDD\ToolkitBundle\Aggregate;
use SymfonyDDD\ToolkitBundle\DependencyInjection\Compiler\ExcludeAggregatesPass;

class ExcludeAggregatesPassTest extends TestCase
{
    private ExcludeAggregatesPass $compilerPass;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->compilerPass = new ExcludeAggregatesPass();
        $this->container = new ContainerBuilder();
    }

    public function testRemovesAggregateDefinitions(): void
    {
        $aggregateDefinition = new Definition(TestAggregate::class);
        $serviceDefinition = new Definition(TestService::class);

        $this->container->setDefinition('test_aggregate', $aggregateDefinition);
        $this->container->setDefinition('test_service', $serviceDefinition);

        $this->compilerPass->process($this->container);

        $this->assertFalse($this->container->hasDefinition('test_aggregate'));
        $this->assertTrue($this->container->hasDefinition('test_service'));
    }

    public function testRemovesAliasesPointingToAggregates(): void
    {
        $aggregateDefinition = new Definition(TestAggregate::class);
        $serviceDefinition = new Definition(TestService::class);

        $this->container->setDefinition('test_aggregate', $aggregateDefinition);
        $this->container->setDefinition('test_service', $serviceDefinition);
        $this->container->setAlias('aggregate_alias', 'test_aggregate');
        $this->container->setAlias('service_alias', 'test_service');

        $this->compilerPass->process($this->container);

        $this->assertFalse($this->container->hasAlias('aggregate_alias'));
        $this->assertTrue($this->container->hasAlias('service_alias'));
    }

    public function testRemovesMultipleAggregateDefinitions(): void
    {
        $aggregate1Definition = new Definition(TestAggregate::class);
        $aggregate2Definition = new Definition(AnotherTestAggregate::class);
        $serviceDefinition = new Definition(TestService::class);

        $this->container->setDefinition('test_aggregate_1', $aggregate1Definition);
        $this->container->setDefinition('test_aggregate_2', $aggregate2Definition);
        $this->container->setDefinition('test_service', $serviceDefinition);

        $this->compilerPass->process($this->container);

        $this->assertFalse($this->container->hasDefinition('test_aggregate_1'));
        $this->assertFalse($this->container->hasDefinition('test_aggregate_2'));
        $this->assertTrue($this->container->hasDefinition('test_service'));
    }

    public function testHandlesEmptyContainer(): void
    {
        $this->compilerPass->process($this->container);
        $this->assertTrue(true);
    }

    public function testDoesNotRemoveNonAggregateClasses(): void
    {
        $serviceDefinition = new Definition(TestService::class);
        $repositoryDefinition = new Definition(TestRepository::class);

        $this->container->setDefinition('test_service', $serviceDefinition);
        $this->container->setDefinition('test_repository', $repositoryDefinition);

        $this->compilerPass->process($this->container);

        $this->assertTrue($this->container->hasDefinition('test_service'));
        $this->assertTrue($this->container->hasDefinition('test_repository'));
    }

    public function testHandlesDefinitionsWithoutClass(): void
    {
        $definitionWithoutClass = new Definition();
        $definitionWithoutClass->setClass(null);
        $aggregateDefinition = new Definition(TestAggregate::class);

        $this->container->setDefinition('no_class_definition', $definitionWithoutClass);
        $this->container->setDefinition('test_aggregate', $aggregateDefinition);

        $this->compilerPass->process($this->container);

        $this->assertTrue($this->container->hasDefinition('no_class_definition'));
        $this->assertFalse($this->container->hasDefinition('test_aggregate'));
    }
}

class TestAggregate extends Aggregate
{
}

class AnotherTestAggregate extends Aggregate
{
}

class TestService
{
}

class TestRepository
{
}
