<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ActionRowComponent;
use App\Entity\Discord\Api\Dto\ButtonComponent;
use App\Entity\Discord\Api\Dto\SelectMenuComponent;
use App\Entity\Discord\Api\Dto\TextInputComponent;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ActionRowComponent
 * @covers \App\Entity\Discord\Api\Dto\AbstractComponent
 */
final class ActionRowComponentTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ActionRowComponent $expected
     * @param ActionRowComponent $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->type, $actual->type);

        self::assertSame(count($expected->components), count($actual->components));

        for ($i = 0; $i < count($expected->components); ++$i) {
            $expectedClassName = get_class($expected->components[$i]);

            self::assertInstanceOf($expectedClassName, $actual->components[$i]);

            switch ($expectedClassName) {
                case ButtonComponent::class:
                    ButtonComponentTest::assertDeepSame($expected->components[$i], $actual->components[$i]);
                    break;
                case TextInputComponent::class:
                    TextInputComponentTest::assertDeepSame($expected->components[$i], $actual->components[$i]);
                    break;
                case SelectMenuComponent::class:
                    SelectMenuComponentTest::assertDeepSame($expected->components[$i], $actual->components[$i]);
                    break;
                default:
                    self::fail('Unexpected type ' . $expectedClassName);
            }
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"type":1,"components":[%s]}';

        $buttonTemplates = [];
        $buttonsExpected = [];

        foreach (ButtonComponentTest::provider_deserialization() as [$buttonTemplate, $buttonExpected]) {
            $buttonTemplates[] = $buttonTemplate;
            $buttonsExpected[] = $buttonExpected;
        }

        $data = [];

        foreach (TextInputComponentTest::provider_deserialization() as [$textTemplate, $textExpected]) {
            $data[] = [sprintf($subjectTemplate, $textTemplate), new ActionRowComponent(components: [$textExpected])];
        }

        foreach (SelectMenuComponentTest::provider_deserialization() as [$selectTemplate, $selectExpected]) {
            $data[] = [sprintf($subjectTemplate, $selectTemplate), new ActionRowComponent(components: [$selectExpected])];
        }

        return [
            [sprintf($subjectTemplate, ''), new ActionRowComponent(components: [])],
            [
                sprintf($subjectTemplate, implode(',', $buttonTemplates)),
                new ActionRowComponent(components: $buttonsExpected)
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param ActionRowComponent $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ActionRowComponent $expected): void
    {
        self::testDeserialization($subject, $expected, ActionRowComponent::class);
    }
}
