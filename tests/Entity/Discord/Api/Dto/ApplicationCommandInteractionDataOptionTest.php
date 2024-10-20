<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ApplicationCommandInteractionDataOption;
use App\Entity\Discord\Api\Enumeration\ApplicationCommandOptionType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ApplicationCommandInteractionDataOption
 */
final class ApplicationCommandInteractionDataOptionTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ApplicationCommandInteractionDataOption $expected
     * @param ApplicationCommandInteractionDataOption $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->value, $actual->value);

        if (isset($expected->options)) {
            self::assertSame(count($expected->options), count($actual->options));

            for ($i = 0; $i < count($expected->options); ++$i) {
                self::assertDeepSame($expected->options[$i], $actual->options[$i]);
            }
        } else {
            self::assertNull($actual->options);
        }

        self::assertSame($expected->focused, $actual->focused);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"name":"test-name","type":%s%s}';

        $values = [6, 7.3, null];

        $data = [];

        foreach (ApplicationCommandOptionType::cases() as $type) {
            $valueIx = array_rand($values);

            $data[] = [
                sprintf($subjectTemplate, $type->value, ',"value":' . ($values[$valueIx] ?? 'null') . ',"options":[{"name":"test-name","type":' . $type->value . ',"value":' . ($values[$valueIx] ?? 'null') . ',"options":[{"name":"test-name","type":' . $type->value . ',"value":' . ($values[$valueIx] ?? 'null') . '}]},{"name":"test-name","type":' . $type->value . ',"value":' . ($values[$valueIx] ?? 'null') . '}],"focused":true'),
                new ApplicationCommandInteractionDataOption(
                    name: 'test-name',
                    type: $type,
                    value: $values[$valueIx],
                    options: [
                        new ApplicationCommandInteractionDataOption(
                            name: 'test-name',
                            type: $type,
                            value: $values[$valueIx],
                            options: [
                                new ApplicationCommandInteractionDataOption(
                                    name: 'test-name',
                                    type: $type,
                                    value: $values[$valueIx]
                                )
                            ]
                        ),
                        new ApplicationCommandInteractionDataOption(
                            name: 'test-name',
                            type: $type,
                            value: $values[$valueIx]
                        )
                    ],
                    focused: true
                )
            ];
        }

        return [
            [
                sprintf($subjectTemplate, ApplicationCommandOptionType::BOOLEAN->value, ''),
                new ApplicationCommandInteractionDataOption(
                    name: 'test-name',
                    type: ApplicationCommandOptionType::BOOLEAN
                )
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param ApplicationCommandInteractionDataOption $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ApplicationCommandInteractionDataOption $expected): void
    {
        self::testDeserialization($subject, $expected, ApplicationCommandInteractionDataOption::class);
    }
}
