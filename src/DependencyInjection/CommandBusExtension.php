<?php

namespace SimpleBus\SymfonyBridge\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class CommandBusExtension extends ConfigurableExtension
{
    private $alias;

    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new CommandBusConfiguration($this->getAlias());
    }

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('command_bus.yml');

        $container->setAlias(
            'simple_bus.command_bus.command_name_resolver',
            'simple_bus.command_bus.' . $mergedConfig['command_name_resolver_strategy'] . '_command_name_resolver'
        );

        if ($mergedConfig['logging']['enabled']) {
            $loader->load('command_bus_logging.yml');
        }

        if ($mergedConfig['unnest_commands']) {
            $container->getDefinition('simple_bus.command_bus.finishes_command_before_handling_next_middleware')
                ->addTag('command_bus_middleware', ['priority' => 1000]);
        }
    }
}
