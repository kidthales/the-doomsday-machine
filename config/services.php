<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->import('parameters/**/*');

    $services = $container->services()->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', '../src/');

    // Concrete implementations for \App\Domain\Jabronibetz\FootyStats\Target\TargetValuesProviderInterface
    $services->set(
        'app.jabronibetz.footy_stats.target.database_target_values_provider',
        \App\Domain\Jabronibetz\FootyStats\Target\DatabaseTargetValuesProvider::class
    );
    $services->set(
        'app.jabronibetz.footy_stats.target.scraper_target_values_provider',
        \App\Domain\Jabronibetz\FootyStats\Target\ScraperTargetValuesProvider::class
    );

    $services->set('app.formatter.ordinal_number_formatter', \NumberFormatter::class)
        ->args(['en-US', \NumberFormatter::ORDINAL]);
};

