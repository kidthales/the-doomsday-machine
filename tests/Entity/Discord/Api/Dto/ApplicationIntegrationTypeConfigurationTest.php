<?php

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ApplicationIntegrationTypeConfiguration;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ApplicationIntegrationTypeConfiguration
 */
final class ApplicationIntegrationTypeConfigurationTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ApplicationIntegrationTypeConfiguration $expected
     * @param ApplicationIntegrationTypeConfiguration $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        if (isset($expected->oauth2_install_params)) {
            InstallParamsTest::assertDeepSame($expected->oauth2_install_params, $actual->oauth2_install_params);
            return;
        }

        self::assertNull($actual->oauth2_install_params);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{%s}';

        $data = [];

        foreach (InstallParamsTest::provider_deserialization() as [$installParamsTemplate, $installParamsExpected]) {
            $data[] = [
                sprintf($subjectTemplate, '"oauth2_install_params":' . $installParamsTemplate),
                new ApplicationIntegrationTypeConfiguration(oauth2_install_params: $installParamsExpected)
            ];
        }

        return [
            [sprintf($subjectTemplate, ''), new ApplicationIntegrationTypeConfiguration()],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param ApplicationIntegrationTypeConfiguration $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ApplicationIntegrationTypeConfiguration $expected): void
    {
        self::testDeserialization($subject, $expected, ApplicationIntegrationTypeConfiguration::class);
    }
}
