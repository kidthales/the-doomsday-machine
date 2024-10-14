<?php

namespace App\DependencyInjection\Compiler;

use App\Console\Style\DefinitionListConverter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @see \App\Console\Style\DefinitionListConverterAwareInterface
 */
class ConsoleStyleDefinitionListConverterPass implements CompilerPassInterface
{
    public const string TAG_NAME = 'app.console_style_definition_list_converter_aware';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(DefinitionListConverter::class)) {
            return;
        }

        $taggedProviders = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach (array_keys($taggedProviders) as $id) {
            $container->findDefinition($id)
                ->addMethodCall(
                    'setDefinitionListConverter',
                    [new Reference(DefinitionListConverter::class)]
                );
        }
    }
}
