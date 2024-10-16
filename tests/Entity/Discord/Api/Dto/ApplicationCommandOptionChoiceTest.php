<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ApplicationCommandOptionChoice;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ApplicationCommandOptionChoice
 */
final class ApplicationCommandOptionChoiceTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ApplicationCommandOptionChoice $expected
     * @param ApplicationCommandOptionChoice $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->name, $actual->name);

        is_int($expected->value)
            ? self::assertEquals($expected->value, $actual->value)
            : self::assertSame($expected->value, $actual->value);

        if (isset($expected->name_localizations)) {
            self::assertIsArray($actual->name_localizations);
            self::assertSame(count($expected->name_localizations), count($actual->name_localizations));

            foreach ($expected->name_localizations as $key => $value) {
                self::assertArrayHasKey($key, $actual->name_localizations);
                self::assertSame($value, $actual->name_localizations[$key]);
            }
        } else {
            self::assertNull($actual->name_localizations);
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"name":"test-name",%s"value":%s}';

        $baseExpected = new ApplicationCommandOptionChoice(name: 'test-name', value: 3.5);

        $withNameLocalizations = clone $baseExpected;
        $withNameLocalizations->name_localizations = ['test-locale-key' => 'test-locale-value'];

        $withIntValue = clone $baseExpected;
        $withIntValue->value = -2;

        $withStringValue = clone $baseExpected;
        $withStringValue->value = 'test-value';

        return [
            [sprintf($subjectTemplate, '', '3.5'), $baseExpected],
            [
                sprintf($subjectTemplate, '"name_localizations":{"test-locale-key":"test-locale-value"},', '3.5'),
                $withNameLocalizations
            ],
            [sprintf($subjectTemplate, '', '-2'), $withIntValue],
            [sprintf($subjectTemplate, '', '"test-value"'), $withStringValue]
        ];
    }

    /**
     * @param string $subject
     * @param ApplicationCommandOptionChoice $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ApplicationCommandOptionChoice $expected): void
    {
        self::testDeserialization($subject, $expected, ApplicationCommandOptionChoice::class);
    }

    /**
     * @return array
     */
    public static function provider_serialization(): array
    {
        $baseSubject = new ApplicationCommandOptionChoice(name: 'test-name', value: 3.5);

        $expectedTemplate = '{"name":"test-name",%s"value":%s}';

        $withNullNameLocalizations = clone $baseSubject;
        $withNullNameLocalizations->normalizeNullNameLocalizations = true;

        $withNameLocalizations = clone $baseSubject;
        $withNameLocalizations->name_localizations = ['test-locale-key' => 'test-locale-value'];

        $withIntValue = clone $baseSubject;
        $withIntValue->value = -2;

        $withStringValue = clone $baseSubject;
        $withStringValue->value = 'test-value';

        return [
            [$baseSubject, sprintf($expectedTemplate, '', '3.5')],
            [$withNullNameLocalizations, sprintf($expectedTemplate, '"name_localizations":null,', '3.5')],
            [
                $withNameLocalizations,
                sprintf($expectedTemplate, '"name_localizations":{"test-locale-key":"test-locale-value"},', '3.5')
            ],
            [$withIntValue, sprintf($expectedTemplate, '', '-2')],
            [$withStringValue, sprintf($expectedTemplate, '', '"test-value"')]
        ];
    }

    /**
     * @param ApplicationCommandOptionChoice $subject
     * @param string $expected
     * @return void
     * @dataProvider provider_serialization
     */
    public function test_serialization(ApplicationCommandOptionChoice $subject, string $expected): void
    {
        self::testSerialization($subject, $expected);
    }
}
