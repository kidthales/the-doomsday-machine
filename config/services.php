<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', __DIR__ . '/../src/')
        ->exclude([
            __DIR__ . '/../src/DependencyInjection/',
            __DIR__ . '/../src/Entity/',
            __DIR__ . '/../src/Kernel.php',
        ]);

    ////////////////////////////////////////////////////////////////////////////
    // Configure \Symfony\Component\Serializer\SerializerInterface
    ////////////////////////////////////////////////////////////////////////////

    $services->set('custom_normalizer', \Symfony\Component\Serializer\Normalizer\CustomNormalizer::class)
        ->tag('serializer.normalizer');

    ////////////////////////////////////////////////////////////////////////////
    // Service Container Optimizations
    ////////////////////////////////////////////////////////////////////////////

    $containerConfigurator->parameters()->set('.container.dumper.inline_factories', true);

    // TODO: Remove the following entries once a given service is no longer removed or inlined when the service container is compiled; otherwise keep it here so we can still test...
    $services->get(\App\HttpClient\ApiClient::class)->public();
    $services->get(\App\Discord\ApiClient::class)->public();
};
