<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ApplicationCommandOption;
use App\Entity\Discord\Api\Enumeration\ApplicationCommandOptionType;
use App\Entity\Discord\Api\Enumeration\ChannelType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ApplicationCommandOption
 */
final class ApplicationCommandOptionTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ApplicationCommandOption $expected
     * @param ApplicationCommandOption $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->description, $actual->description);

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

        if (isset($expected->description_localizations)) {
            self::assertIsArray($actual->description_localizations);
            self::assertSame(count($expected->description_localizations), count($actual->description_localizations));

            foreach ($expected->description_localizations as $key => $value) {
                self::assertArrayHasKey($key, $actual->description_localizations);
                self::assertSame($value, $actual->description_localizations[$key]);
            }
        } else {
            self::assertNull($actual->description_localizations);
        }

        self::assertSame($expected->required, $actual->required);

        if (isset($expected->choices)) {
            self::assertIsArray($actual->choices);
            self::assertSame(count($expected->choices), count($actual->choices));

            for ($i = 0; $i < count($expected->choices); ++$i) {
                ApplicationCommandOptionChoiceTest::assertDeepSame($expected->choices[$i], $actual->choices[$i]);
            }
        } else {
            self::assertNull($actual->choices);
        }

        if (isset($expected->options)) {
            self::assertIsArray($actual->options);
            self::assertSame(count($expected->options), count($actual->options));

            for ($i = 0; $i < count($expected->options); ++$i) {
                self::assertDeepSame($expected->options[$i], $actual->options[$i]);
            }
        } else {
            self::assertNull($actual->options);
        }

        if (isset($expected->channel_types)) {
            self::assertIsArray($actual->channel_types);
            self::assertSame(count($expected->channel_types), count($actual->channel_types));

            for ($i = 0; $i < count($expected->channel_types); ++$i) {
                self::assertSame($expected->channel_types[$i], $actual->channel_types[$i]);
            }
        } else {
            self::assertNull($actual->channel_types);
        }

        is_int($expected->min_value)
            ? self::assertEquals($expected->min_value, $actual->min_value)
            : self::assertSame($expected->min_value, $actual->min_value);
        is_int($expected->max_value)
            ? self::assertEquals($expected->max_value, $actual->max_value)
            : self::assertSame($expected->max_value, $actual->max_value);
        self::assertSame($expected->min_length, $actual->min_length);
        self::assertSame($expected->max_length, $actual->max_length);
        self::assertSame($expected->autocomplete, $actual->autocomplete);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"type":3,"name":"test-name",%s"description":"test-description"%s}';

        $baseExpected = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::STRING,
            name: 'test-name',
            description: 'test-description'
        );

        $withNameLocalizations = clone $baseExpected;
        $withNameLocalizations->name_localizations = ['test-locale-key' => 'test-locale-value'];

        $withChoices = clone $baseExpected;
        $withChoices->choices = [];
        $withChoicesSubject = [];

        foreach (ApplicationCommandOptionChoiceTest::provider_deserialization() as $dataset) {
            $withChoicesSubject[] = $dataset[0];
            $withChoices->choices[] = $dataset[1];
        }

        $withEmptyOptions = clone $baseExpected;
        $withEmptyOptions->options = [];

        $withOptions = clone $baseExpected;
        $withOptions->options = [clone $baseExpected];

        $withChannelTypes = clone $baseExpected;
        $withChannelTypes->channel_types = ChannelType::cases();

        $withMinValue = clone $baseExpected;
        $withMinValue->min_value = 3.14159;

        $withMaxValue = clone $baseExpected;
        $withMaxValue->max_value = -18;

        return [
            [sprintf($subjectTemplate, '', ''), $baseExpected],
            [
                sprintf($subjectTemplate, '"name_localizations":{"test-locale-key":"test-locale-value"},', ''),
                $withNameLocalizations
            ],
            [sprintf($subjectTemplate, '', sprintf(',"choices":[%s]', implode(',', $withChoicesSubject))), $withChoices],
            [sprintf($subjectTemplate, '', ',"options":[]'), $withEmptyOptions],
            [
                sprintf($subjectTemplate, '', ',"options":[{"type":3,"name":"test-name","description":"test-description"}]'),
                $withOptions
            ],
            [sprintf($subjectTemplate, '', ',"channel_types":[0,1,2,3,4,5,10,11,12,13,14,15,16]'), $withChannelTypes],
            [sprintf($subjectTemplate, '', ',"min_value":3.14159'), $withMinValue],
            [sprintf($subjectTemplate, '', ',"max_value":-18'), $withMaxValue]
        ];
    }

    /**
     * @param string $subject
     * @param ApplicationCommandOption $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ApplicationCommandOption $expected): void
    {
        self::testDeserialization($subject, $expected, ApplicationCommandOption::class);
    }

    /**
     * @return array
     */
    public static function provider_serialization(): array
    {
        $baseSubject = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::STRING,
            name: 'test-name',
            description: 'test-description'
        );

        $expectedTemplate = '{"type":3,"name":"test-name",%s"description":"test-description"%s}';

        $withNullNameLocalizations = clone $baseSubject;
        $withNullNameLocalizations->normalizeNullNameLocalizations = true;

        $withNameLocalizations = clone $baseSubject;
        $withNameLocalizations->name_localizations = ['test-locale-key' => 'test-locale-value'];

        $withNullDescriptionLocalizations = clone $baseSubject;
        $withNullDescriptionLocalizations->normalizeNullDescriptionLocalizations = true;

        $withDescriptionLocalizations = clone $baseSubject;
        $withDescriptionLocalizations->description_localizations = ['test-locale-key' => 'test-locale-value'];

        $withRequired = clone $baseSubject;
        $withRequired->required = false;

        $withChoices = clone $baseSubject;
        $withChoices->choices = [];
        $withChoicesExpected = [];

        foreach (ApplicationCommandOptionChoiceTest::provider_serialization() as $dataset) {
            $withChoices->choices[] = $dataset[0];
            $withChoicesExpected[] = $dataset[1];
        }

        $withEmptyOptions = clone $baseSubject;
        $withEmptyOptions->options = [];

        $withOptions = clone $baseSubject;
        $withOptions->options = [clone $baseSubject];

        $withChannelTypes = clone $baseSubject;
        $withChannelTypes->channel_types = ChannelType::cases();

        $withMinValue = clone $baseSubject;
        $withMinValue->min_value = 3.14159;

        $withMaxValue = clone $baseSubject;
        $withMaxValue->max_value = -18;

        $withMinLength = clone $baseSubject;
        $withMinLength->min_length = 0;

        $withMaxLength = clone $baseSubject;
        $withMaxLength->max_length = 10;

        $withAutocomplete = clone $baseSubject;
        $withAutocomplete->autocomplete = true;

        return [
            [$baseSubject, sprintf($expectedTemplate, '', '')],
            [$withNullNameLocalizations, sprintf($expectedTemplate, '"name_localizations":null,', '')],
            [
                $withNameLocalizations,
                sprintf($expectedTemplate, '"name_localizations":{"test-locale-key":"test-locale-value"},', '')
            ],
            [$withNullDescriptionLocalizations, sprintf($expectedTemplate, '', ',"description_localizations":null')],
            [
                $withDescriptionLocalizations,
                sprintf($expectedTemplate, '', ',"description_localizations":{"test-locale-key":"test-locale-value"}')
            ],
            [$withRequired, sprintf($expectedTemplate, '', ',"required":false')],
            [
                $withChoices,
                sprintf($expectedTemplate, '', sprintf(',"choices":[%s]', implode(',', $withChoicesExpected)))
            ],
            [$withEmptyOptions, sprintf($expectedTemplate, '', ',"options":[]')],
            [$withOptions, sprintf($expectedTemplate, '', ',"options":[{"type":3,"name":"test-name","description":"test-description"}]')],
            [$withChannelTypes, sprintf($expectedTemplate, '', ',"channel_types":[0,1,2,3,4,5,10,11,12,13,14,15,16]')],
            [$withMinValue, sprintf($expectedTemplate, '', ',"min_value":3.14159')],
            [$withMaxValue, sprintf($expectedTemplate, '', ',"max_value":-18')],
            [$withMinLength, sprintf($expectedTemplate, '', ',"min_length":0')],
            [$withMaxLength, sprintf($expectedTemplate, '', ',"max_length":10')],
            [$withAutocomplete, sprintf($expectedTemplate, '', ',"autocomplete":true')]
        ];
    }

    /**
     * @param ApplicationCommandOption $subject
     * @param string $expected
     * @return void
     * @dataProvider provider_serialization
     */
    public function test_serialization(ApplicationCommandOption $subject, string $expected): void
    {
        self::testSerialization($subject, $expected);
    }
}
