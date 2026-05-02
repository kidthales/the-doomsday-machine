<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->import('parameters/**/*');

    $services = $container->services()->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', '../src/');

    $services->set('app.shared.formatter.ordinal_number_formatter', \NumberFormatter::class)
        ->args(['en-US', \NumberFormatter::ORDINAL]);
};

