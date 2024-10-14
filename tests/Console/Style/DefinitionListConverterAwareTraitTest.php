<?php

namespace App\Tests\Console\Style;

use App\Console\Style\DefinitionListConverter;
use App\Console\Style\DefinitionListConverterAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\Console\Style\DefinitionListConverterAwareTrait
 */
final class DefinitionListConverterAwareTraitTest extends KernelTestCase
{
    /**
     * @return void
     */
    public function test(): void
    {
        self::bootKernel();

        $converter = self::getContainer()->get(DefinitionListConverter::class);

        $subject = new DefinitionListConverterAware();

        $subject->setDefinitionListConverter($converter);

        self::assertSame($converter, $subject->getDefinitionListConverter());
    }
}

class DefinitionListConverterAware
{
    use DefinitionListConverterAwareTrait;

    /**
     * @return DefinitionListConverter
     */
    public function getDefinitionListConverter(): DefinitionListConverter
    {
        return $this->definitionListConverter;
    }
}
