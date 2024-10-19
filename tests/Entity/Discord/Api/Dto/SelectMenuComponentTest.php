<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\SelectMenuComponent;
use App\Entity\Discord\Api\Enumeration\ChannelType;
use App\Entity\Discord\Api\Enumeration\ComponentType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\SelectMenuComponent
 * @covers \App\Entity\Discord\Api\Dto\AbstractComponent
 */
final class SelectMenuComponentTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param SelectMenuComponent $expected
     * @param SelectMenuComponent $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->custom_id, $actual->custom_id);

        if (isset($expected->options)) {
            self::assertSame(count($expected->options), count($actual->options));

            for ($i = 0; $i < count($expected->options); ++$i) {
                SelectOptionTest::assertDeepSame($expected->options[$i], $actual->options[$i]);
            }
        } else {
            self::assertNull($actual->options);
        }

        if (isset($expected->channel_types)) {
            self::assertSame(count($expected->channel_types), count($actual->channel_types));

            for ($i = 0; $i < count($expected->channel_types); ++$i) {
                self::assertSame($expected->channel_types[$i], $actual->channel_types[$i]);
            }
        }

        self::assertSame($expected->placeholder, $actual->placeholder);

        if (isset($expected->default_values)) {
            self::assertSame(count($expected->default_values), count($actual->default_values));

            for ($i = 0; $i < count($expected->default_values); ++$i) {
                SelectDefaultValueTest::assertDeepSame($expected->default_values[$i], $actual->default_values[$i]);
            }
        } else {
            self::assertNull($actual->default_values);
        }

        self::assertSame($expected->min_values, $actual->min_values);
        self::assertSame($expected->max_values, $actual->max_values);
        self::assertSame($expected->disabled, $actual->disabled);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"type":%s,"custom_id":"%s"%s}';

        $optTemplates = [];
        $optsExpected = [];

        foreach (SelectOptionTest::provider_deserialization() as [$optTemplate, $optExpected]) {
            $optTemplates[] = $optTemplate;
            $optsExpected[] = $optExpected;
        }

        $valueTemplates = [];
        $valuesExpected = [];

        foreach (SelectDefaultValueTest::provider_deserialization() as [$valueTemplate, $valueExpected]) {
            $valueTemplates[] = $valueTemplate;
            $valuesExpected[] = $valueExpected;
        }

        $data = [];

        foreach ([ComponentType::StringSelect, ComponentType::UserSelect, ComponentType::RoleSelect, ComponentType::MentionableSelect, ComponentType::ChannelSelect] as $type) {
            $data[] = [
                sprintf($subjectTemplate, $type->value, 'test-custom-id', ',"options":[' . implode(',', $optTemplates) . '],"channel_types":[' . implode(',', array_column(ChannelType::cases(), 'value')) . '],"placeholder":"test-placeholder","default_values":[' . implode(',', $valueTemplates) . '],"min_values":1,"max_values":1,"disabled":false'),
                new SelectMenuComponent(
                    type: $type,
                    custom_id: 'test-custom-id',
                    options: $optsExpected,
                    channel_types: ChannelType::cases(),
                    placeholder: 'test-placeholder',
                    default_values: $valuesExpected,
                    min_values: 1,
                    max_values: 1,
                    disabled: false
                )
            ];
        }

        return [
            [
                sprintf($subjectTemplate, ComponentType::StringSelect->value, 'test-custom-id', ''),
                new SelectMenuComponent(type: ComponentType::StringSelect, custom_id: 'test-custom-id')
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param SelectMenuComponent $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, SelectMenuComponent $expected): void
    {
        self::testDeserialization($subject, $expected, SelectMenuComponent::class);
    }
}
