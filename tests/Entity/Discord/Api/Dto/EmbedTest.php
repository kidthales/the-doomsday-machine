<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\Embed;
use App\Entity\Discord\Api\Dto\EmbedFooter;
use App\Entity\Discord\Api\Enumeration\EmbedType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\Embed
 */
final class EmbedTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param Embed $expected
     * @param Embed $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->title, $actual->title);
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->description, $actual->description);
        self::assertSame($expected->url, $actual->url);
        self::assertSame($expected->timestamp, $actual->timestamp);
        self::assertSame($expected->color, $actual->color);

        if (isset($expected->footer)) {
            EmbedFooterTest::assertDeepSame($expected->footer, $actual->footer);
        } else {
            self::assertNull($actual->footer);
        }

        if (isset($expected->image)) {
            EmbedImageTest::assertDeepSame($expected->image, $actual->image);
        } else {
            self::assertNull($actual->image);
        }

        if (isset($expected->thumbnail)) {
            EmbedThumbnailTest::assertDeepSame($expected->thumbnail, $actual->thumbnail);
        } else {
            self::assertNull($actual->thumbnail);
        }

        if (isset($expected->video)) {
            EmbedVideoTest::assertDeepSame($expected->video, $actual->video);
        } else {
            self::assertNull($actual->video);
        }

        if (isset($expected->provider)) {
            EmbedProviderTest::assertDeepSame($expected->provider, $actual->provider);
        } else {
            self::assertNull($actual->provider);
        }

        if (isset($expected->author)) {
            EmbedAuthorTest::assertDeepSame($expected->author, $actual->author);
        } else {
            self::assertNull($actual->author);
        }

        if (isset($expected->fields)) {
            self::assertSame(count($expected->fields), count($actual->fields));

            for ($i = 0; $i < count($expected->fields); ++$i) {
                EmbedFieldTest::assertDeepSame($expected->fields[$i], $actual->fields[$i]);
            }
        } else {
            self::assertNull($actual->fields);
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{%s}';

        $fieldTemplates = [];
        $fieldsExpected = [];

        foreach (EmbedFieldTest::provider_deserialization() as [$fieldTemplate, $fieldExpected]) {
            $fieldTemplates[] = $fieldTemplate;
            $fieldsExpected[] = $fieldExpected;
        }

        $data = [];

        foreach (EmbedFooterTest::provider_deserialization() as [$footerTemplate, $footerExpected]) {
            $data[] = [
                sprintf($subjectTemplate, '"title":"test-title","type":"' . EmbedType::rich->value . '","footer":' . $footerTemplate . ',"fields":[' . implode(',', $fieldTemplates) . ']'),
                new Embed(title: 'test-title', type: EmbedType::rich, footer: $footerExpected, fields: $fieldsExpected)
            ];
        }

        foreach (EmbedImageTest::provider_deserialization() as [$imageTemplate, $imageExpected]) {
            $data[] = [
                sprintf($subjectTemplate, '"title":"test-title","type":"' . EmbedType::image->value . '","image":' . $imageTemplate . ',"fields":[' . implode(',', $fieldTemplates) . ']'),
                new Embed(title: 'test-title', type: EmbedType::image, image: $imageExpected, fields: $fieldsExpected)
            ];
        }

        foreach (EmbedThumbnailTest::provider_deserialization() as [$thumbnailTemplate, $thumbnailExpected]) {
            $data[] = [
                sprintf($subjectTemplate, '"title":"test-title","type":"' . EmbedType::article->value . '","thumbnail":' . $thumbnailTemplate . ',"fields":[' . implode(',', $fieldTemplates) . ']'),
                new Embed(title: 'test-title', type: EmbedType::article, thumbnail: $thumbnailExpected, fields: $fieldsExpected)
            ];
        }

        foreach (EmbedVideoTest::provider_deserialization() as [$videoTemplate, $videoExpected]) {
            $data[] = [
                sprintf($subjectTemplate, '"title":"test-title","type":"' . EmbedType::video->value . '","video":' . $videoTemplate . ',"fields":[' . implode(',', $fieldTemplates) . ']'),
                new Embed(title: 'test-title', type: EmbedType::video, video: $videoExpected, fields: $fieldsExpected)
            ];
        }

        foreach (EmbedProviderTest::provider_deserialization() as [$providerTemplate, $providerExpected]) {
            $data[] = [
                sprintf($subjectTemplate, '"title":"test-title","type":"' . EmbedType::link->value . '","provider":' . $providerTemplate . ',"fields":[' . implode(',', $fieldTemplates) . ']'),
                new Embed(title: 'test-title', type: EmbedType::link, provider: $providerExpected, fields: $fieldsExpected)
            ];
        }

        foreach (EmbedAuthorTest::provider_deserialization() as [$authorTemplate, $authorExpected]) {
            $data[] = [
                sprintf($subjectTemplate, '"title":"test-title","type":"' . EmbedType::link->value . '","author":' . $authorTemplate . ',"fields":[' . implode(',', $fieldTemplates) . ']'),
                new Embed(title: 'test-title', type: EmbedType::link, author: $authorExpected, fields: $fieldsExpected)
            ];
        }

        return [
            [sprintf($subjectTemplate, ''), new Embed()],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param Embed $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, Embed $expected): void
    {
        self::testDeserialization($subject, $expected, Embed::class);
    }
}
