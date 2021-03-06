<?php

declare(strict_types=1);

namespace WArslett\TableBuilderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('table_builder');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('html_renderer')->end()
                ->arrayNode('twig_renderer')
                    ->children()
                        ->scalarNode('theme_template')->end()
                        ->arrayNode('cell_value_blocks')
                            ->useAttributeAsKey('column')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('cell_value_templates')
                            ->useAttributeAsKey('column')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('phtml_renderer')
                    ->children()
                        ->scalarNode('theme_directory')->end()
                        ->arrayNode('cell_value_templates')
                            ->useAttributeAsKey('column')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
