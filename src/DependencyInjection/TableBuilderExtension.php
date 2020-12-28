<?php

declare(strict_types=1);

namespace WArslett\TableBuilderBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use WArslett\TableBuilder\Renderer\Csv\CsvRenderer;
use WArslett\TableBuilder\Renderer\Html\HtmlTableRendererInterface;
use WArslett\TableBuilder\Renderer\Html\TwigRenderer;
use WArslett\TableBuilder\Renderer\Html\PhtmlRenderer;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

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

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        if (isset($config['html_renderer'])) {
            $htmlRendererDefinition = $container->getDefinition($config['html_renderer']);
            $container->setDefinition(HtmlTableRendererInterface::class, $htmlRendererDefinition);
        }

        if (isset($config['twig_renderer'])) {
            $twigRendererConfig = $config['twig_renderer'];
            $twigRendererDefinition = $container->getDefinition(TwigRenderer::class);

            if (isset($twigRendererConfig['theme_template'])) {
                $twigRendererDefinition->replaceArgument(
                    '$themeTemplatePath',
                    $twigRendererConfig['theme_template']
                );
            }

            if (isset($twigRendererConfig['cell_value_blocks'])) {
                foreach ($twigRendererConfig['cell_value_blocks'] as $renderingType => $block) {
                    $twigRendererDefinition->addMethodCall('registerCellValueBlock', [$renderingType, $block]);
                }
            }

            if (isset($twigRendererConfig['cell_value_templates'])) {
                foreach ($twigRendererConfig['cell_value_templates'] as $renderingType => $templatePath) {
                    $twigRendererDefinition->addMethodCall(
                        'registerCellValueTemplate',
                        [$renderingType, $templatePath]
                    );
                }
            }
        }

        if (isset($config['phtml_renderer'])) {
            $phtmlRendererConfig = $config['phtml_renderer'];
            $phtmlRendererDefinition = $container->getDefinition(PhtmlRenderer::class);

            if (isset($phtmlRendererConfig['theme_directory'])) {
                $phtmlRendererDefinition->replaceArgument(
                    '$themeDirectoryPath',
                    $phtmlRendererConfig['theme_directory']
                );
            }

            if (isset($phtmlRendererConfig['cell_value_templates'])) {
                foreach ($phtmlRendererConfig['cell_value_templates'] as $renderingType => $templatePath) {
                    $phtmlRendererDefinition->addMethodCall(
                        'registerCellValueTemplate',
                        [$renderingType, $templatePath]
                    );
                }
            }
        }
    }
}
