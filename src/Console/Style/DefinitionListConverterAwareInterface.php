<?php

namespace App\Console\Style;

use App\DependencyInjection\Compiler\ConsoleStyleDefinitionListConverterPass;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @see ConsoleStyleDefinitionListConverterPass
 * @see DefinitionListConverterAwareTrait
 */
#[AutoconfigureTag(name: ConsoleStyleDefinitionListConverterPass::TAG_NAME)]
interface DefinitionListConverterAwareInterface
{
    /**
     * @param DefinitionListConverter $definitionListConverter
     * @return void
     * @internal
     */
    public function setDefinitionListConverter(DefinitionListConverter $definitionListConverter): void;
}
