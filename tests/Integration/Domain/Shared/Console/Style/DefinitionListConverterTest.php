<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\Shared\Console\Style;

use App\Domain\Shared\Console\Style\DefinitionListConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;

/**
  * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('shared')]
#[CoversClass(DefinitionListConverter::class)]
final class DefinitionListConverterTest extends KernelTestCase
{
    /**
     * @throws SerializerExceptionInterface
     */
    #[Test]
    #[TestWith([null, [null]], 'null')]
    #[TestWith(['test', ['test']], "'test'")]
    #[TestWith(
        [['test-key-1' => 'test-value-1'], [['test-key-1' => 'test-value-1']]],
        "['test-key-1' => 'test-value-1']"
    )]
    #[TestWith(
        [
            ['test-key-1' => ['nested-test-key' => 'test-value-1'], 'test-key-2' => 'test-value-2'],
            [['test-key-1.nested-test-key' => 'test-value-1'], ['test-key-2' => 'test-value-2']]
        ],
        "['test-key-1' => ['nested-test-key' => 'test-value-1'], 'test-key-2' => 'test-value-2']"
    )]
    #[TestWith(
        [
            ['test-key-1' => ['test-value-1'], 'test-key-2' => 'test-value-2'],
            [['test-key-1[0]' => 'test-value-1'], ['test-key-2' => 'test-value-2']]
        ],
        "['test-key-1' => ['test-value-1'], 'test-key-2' => 'test-value-2']"
    )]
    #[TestWith(
        [
            ['test-key-1' => [['nested-test-key' => 'test-value-1']], 'test-key-2' => 'test-value-2'],
            [['test-key-1[0].nested-test-key' => 'test-value-1'], ['test-key-2' => 'test-value-2']]
        ],
        "['test-key-1' => [['nested-test-key' => 'test-value-1']], 'test-key-2' => 'test-value-2']"
    )]
    public function it_converts_subject_into_flattened_definition_list_arrays(mixed $subject, array $expected): void
    {
        $this->bootKernel();

        /** @var DefinitionListConverter $converter */
        $converter = $this->getContainer()->get(DefinitionListConverter::class);
        $actual = $converter->convert($subject);

        $this->assertSame(count($expected), count($actual));

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $actual[$key]);
        }
    }
}
