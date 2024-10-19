<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ActionRowComponent;
use App\Entity\Discord\Api\Dto\ButtonComponent;
use App\Entity\Discord\Api\Dto\ModalSubmitData;
use App\Entity\Discord\Api\Dto\SelectMenuComponent;
use App\Entity\Discord\Api\Dto\TextInputComponent;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ModalSubmitData
 */
final class ModalSubmitDataTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ModalSubmitData $expected
     * @param ModalSubmitData $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->custom_id, $actual->custom_id);

        self::assertSame(count($expected->components), count($actual->components));

        for ($i = 0; $i < count($expected->components); ++$i) {
            $className = get_class($expected->components[$i]);

            self::assertInstanceOf($className, $actual->components[$i]);

            switch ($className) {
                case ActionRowComponent::class:
                    ActionRowComponentTest::assertDeepSame($expected->components[$i], $actual->components[$i]);
                    break;
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
                    self::fail('Unexpected component type: ' . $className);
            }
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"custom_id":"test-id","components":[%s]}';

        $actionTemplates = [];
        $actionsExpected = [];

        foreach (ActionRowComponentTest::provider_deserialization() as [$actionTemplate, $actionExpected]) {
            $actionTemplates[] = $actionTemplate;
            $actionsExpected[] = $actionExpected;
        }

        $buttonTemplates = [];
        $buttonsExpected = [];

        foreach (ButtonComponentTest::provider_deserialization() as [$buttonTemplate, $buttonExpected]) {
            $buttonTemplates[] = $buttonTemplate;
            $buttonsExpected[] = $buttonExpected;
        }

        $textTemplates = [];
        $textsExpected = [];

        foreach (TextInputComponentTest::provider_deserialization() as [$textTemplate, $textExpected]) {
            $textTemplates[] = $textTemplate;
            $textsExpected[] = $textExpected;
        }

        $selectTemplates = [];
        $selectsExpected = [];

        foreach (SelectMenuComponentTest::provider_deserialization() as [$selectTemplate, $selectExpected]) {
            $selectTemplates[] = $selectTemplate;
            $selectsExpected[] = $selectExpected;
        }

        return [
            [sprintf($subjectTemplate, ''), new ModalSubmitData(custom_id: 'test-id', components: [])],
            [
                sprintf($subjectTemplate, implode(',', $actionTemplates)),
                new ModalSubmitData(custom_id: 'test-id', components: $actionsExpected)
            ],
            [
                sprintf($subjectTemplate, implode(',', $buttonTemplates)),
                new ModalSubmitData(custom_id: 'test-id', components: $buttonsExpected)
            ],
            [
                sprintf($subjectTemplate, implode(',', $textTemplates)),
                new ModalSubmitData(custom_id: 'test-id', components: $textsExpected)
            ],
            [
                sprintf($subjectTemplate, implode(',', $selectTemplates)),
                new ModalSubmitData(custom_id: 'test-id', components: $selectsExpected)
            ]
        ];
    }

    /**
     * @param string $subject
     * @param ModalSubmitData $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ModalSubmitData $expected): void
    {
        self::testDeserialization($subject, $expected, ModalSubmitData::class);
    }
}
