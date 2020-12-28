<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use WArslett\TableBuilder\Renderer\Csv\CsvRenderer;
use WArslett\TableBuilder\Renderer\Html\HtmlTableRendererInterface;
use WArslett\TableBuilder\Renderer\Html\PhtmlRenderer;
use WArslett\TableBuilder\Renderer\Html\TwigRenderer;
use WArslett\TableBuilder\RouteGeneratorAdapter\RouteGeneratorAdapterInterface;
use WArslett\TableBuilder\RouteGeneratorAdapter\SprintfAdapter;
use WArslett\TableBuilder\RouteGeneratorAdapter\SymfonyRoutingAdapter;
use WArslett\TableBuilder\TableBuilderFactory;
use WArslett\TableBuilder\TableBuilderFactoryInterface;
use WArslett\TableBuilder\Twig\StandardTemplatesLoader;
use WArslett\TableBuilder\Twig\TableRendererExtension;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire();

    $services->set(TableBuilderFactory::class);
    $services->alias(TableBuilderFactoryInterface::class, TableBuilderFactory::class);

    $services->set(SprintfAdapter::class);
    $services->set(SymfonyRoutingAdapter::class);
    $services->alias(RouteGeneratorAdapterInterface::class, SymfonyRoutingAdapter::class);

    $services->set(PhtmlRenderer::class)
        ->arg('$routeGeneratorAdapter', service(RouteGeneratorAdapterInterface::class))
        ->arg('$themeDirectoryPath', PhtmlRenderer::STANDARD_THEME_DIRECTORY);

    $services->set(StandardTemplatesLoader::class)->tag('twig.loader');
    $services->set(TableRendererExtension::class)->tag('twig.extension');
    $services->set(TwigRenderer::class)
        ->arg('$routeGeneratorAdapter', service(RouteGeneratorAdapterInterface::class))
        ->arg('$themeTemplatePath', TwigRenderer::STANDARD_THEME_PATH);

    $services->alias(HtmlTableRendererInterface::class, TwigRenderer::class);

    $services->set(CsvRenderer::class);
};
