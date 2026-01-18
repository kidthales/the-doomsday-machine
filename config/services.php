<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->import('parameters/**/*');

    $services = $container->services()->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\',  '../src/');

    $services->set(
        'app.provider.footy_stats.database_target_arguments_provider',
        \App\Provider\FootyStats\DatabaseTargetArgumentsProvider::class
    );

    $services->set(
        'app.provider.footy_stats.scraper_target_arguments_provider',
        \App\Provider\FootyStats\ScraperTargetArgumentsProvider::class
    );
};

