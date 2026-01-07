<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('app.file_depot_path', '%kernel.project_dir%/data/%kernel.environment%/file_depot');

    $services = $container->services()->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\',  '../src/');
};

