<?php

namespace App;

use App\DependencyInjection\Compiler\ConsoleStyleDefinitionListConverterPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * @codeCoverageIgnore
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @param ContainerBuilder $container
     * @return void
     */
    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ConsoleStyleDefinitionListConverterPass());
    }
}
