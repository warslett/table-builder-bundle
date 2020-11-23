<?php

declare(strict_types=1);

namespace WArslett\TableBuilderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('table_builder');

        $treeBuilder->getRootNode()
            ->children()
              ->arrayNode('twig_renderer')
                ->children()
                  ->scalarNode('theme_template')->end()
                ->end()
              ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
