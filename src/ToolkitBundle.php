<?php
declare(strict_types=1);
namespace SymfonyDDD\ToolkitBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use SymfonyDDD\ToolkitBundle\DependencyInjection\Compiler\CommandRouterPass;
use SymfonyDDD\ToolkitBundle\DependencyInjection\Compiler\ExcludeAggregatesPass;
use SymfonyDDD\ToolkitBundle\DependencyInjection\ToolkitExtension;

class ToolkitBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ToolkitExtension
    {
        return new ToolkitExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CommandRouterPass());
        $container->addCompilerPass(new ExcludeAggregatesPass());
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');
    }
}
