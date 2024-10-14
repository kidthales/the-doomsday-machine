<?php

declare(strict_types=1);

namespace App\Tests\DependencyInjection\Compiler;

use App\DependencyInjection\Compiler\ConsoleStyleDefinitionListConverterPass;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \App\DependencyInjection\Compiler\ConsoleStyleDefinitionListConverterPass
 */
final class ConsoleStyleDefinitionListConverterPassTest extends KernelTestCase
{
    /**
     * @return void
     */
    public function test_process_noService(): void
    {
        $pass = new ConsoleStyleDefinitionListConverterPass();

        $containerBuilder = self::createMock(ContainerBuilder::class);
        $containerBuilder->method('has')->willReturn(false);

        $pass->process($containerBuilder);

        self::assertTrue(true);
    }

    /**
     * @return void
     */
    public function test_process_noTaggedServiceIds(): void
    {
        $pass = new ConsoleStyleDefinitionListConverterPass();

        $containerBuilder = self::createMock(ContainerBuilder::class);
        $containerBuilder->method('has')->willReturn(true);
        $containerBuilder->method('findTaggedServiceIds')->willReturn([]);

        $pass->process($containerBuilder);

        self::assertTrue(true);
    }

    /**
     * @return void
     */
    public function test_process(): void
    {
        $pass = new ConsoleStyleDefinitionListConverterPass();

        $containerBuilder = self::createMock(ContainerBuilder::class);
        $containerBuilder->method('has')->willReturn(true);
        $containerBuilder->method('findTaggedServiceIds')->willReturn(['test.service' => stdClass::class]);
        $containerBuilder->method('findDefinition')->willReturn(new Definition());

        $pass->process($containerBuilder);

        self::assertTrue(true);
    }
}
