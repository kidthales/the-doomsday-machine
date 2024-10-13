<?php

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\InstallParams;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\InstallParams
 */
final class InstallParamsTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param InstallParams $expected
     * @param InstallParams $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame(count($expected->scopes), count($actual->scopes));

        for ($i = 0; $i < count($expected->scopes); ++$i) {
            self::assertSame($expected->scopes[$i], $actual->scopes[$i]);
        }

        self::assertSame($expected->permissions, $actual->permissions);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"scopes":[%s],"permissions":"test-permissions"}';

        return [
            [
                sprintf($subjectTemplate, ''),
                new InstallParams(scopes: [], permissions: 'test-permissions')
            ],
            [
                sprintf($subjectTemplate, '"test-scope-1","test-scope-2"'),
                new InstallParams(scopes: ['test-scope-1', 'test-scope-2'], permissions: 'test-permissions')
            ]
        ];
    }

    /**
     * @param string $subject
     * @param InstallParams $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, InstallParams $expected): void
    {
        self::testDeserialization($subject, $expected, InstallParams::class);
    }
}
