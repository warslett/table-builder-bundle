<?php

declare(strict_types=1);

namespace WArslett\TableBuilderBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use WArslett\TableBuilder\Renderer\Html\TwigRenderer;

final class TableBuilderExtension extends Extension
{

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        if (isset($config['twig_renderer'])) {
            $twigRendererDefinition = $container->getDefinition(TwigRenderer::class);

            if (isset($config['twig_renderer']['theme_template'])) {
                $twigRendererDefinition->replaceArgument(
                    '$themeTemplatePath',
                    $config['twig_renderer']['theme_template']
                );
            }

            if (isset($config['twig_renderer']['cell_value_blocks'])) {
                foreach ($config['twig_renderer']['cell_value_blocks'] as $renderingType => $block) {
                    $twigRendererDefinition->addMethodCall('registerCellValueBlock', [$renderingType, $block]);
                }
            }

            if (isset($config['twig_renderer']['cell_value_templates'])) {
                foreach ($config['twig_renderer']['cell_value_templates'] as $renderingType => $templatePath) {
                    $twigRendererDefinition->addMethodCall(
                        'registerCellValueTemplate',
                        [$renderingType, $templatePath]
                    );
                }
            }
        }
    }
}
