<?php

declare(strict_types=1);

namespace WArslett\TableBuilderBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WArslett\TableBuilderBundle\DependencyInjection\CompilerPass;

final class TableBuilderBundle extends Bundle
{

    /**
     * @param ContainerBuilder $container
     * @return void
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CompilerPass());
    }
}
