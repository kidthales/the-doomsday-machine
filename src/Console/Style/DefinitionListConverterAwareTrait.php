<?php

declare(strict_types=1);

namespace App\Console\Style;

/**
 * @see DefinitionListConverterAwareInterface
 */
trait DefinitionListConverterAwareTrait
{
    /**
     * @var DefinitionListConverter
     */
    protected DefinitionListConverter $definitionListConverter;

    /**
     * @param DefinitionListConverter $converter
     * @return void
     */
    public function setDefinitionListConverter(DefinitionListConverter $converter): void
    {
        $this->definitionListConverter = $converter;
    }
}
