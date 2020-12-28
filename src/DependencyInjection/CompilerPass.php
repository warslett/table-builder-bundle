<?php

declare(strict_types=1);

namespace WArslett\TableBuilderBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use WArslett\TableBuilder\Renderer\Csv\CsvRenderer;

final class CompilerPass implements CompilerPassInterface
{

    /**
     * @param ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        $csvRendererDefinition = $container->findDefinition(CsvRenderer::class);
        $transformerDefinitions = $container->findTaggedServiceIds('table_builder.csv_cell_value_transformer');
        foreach ($transformerDefinitions as $id => $tags) {
            foreach ($tags as $attributes) {
                $csvRendererDefinition->addMethodCall(
                    'registerCellValueTransformer',
                    [$attributes['rendering_type'], new Reference($id)]
                );
            }
        }
    }
}
