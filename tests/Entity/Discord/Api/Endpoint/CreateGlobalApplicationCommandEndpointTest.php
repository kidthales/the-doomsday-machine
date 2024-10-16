<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Endpoint;

use App\Entity\Discord\Api\Dto\ApplicationCommand;
use App\Entity\Discord\Api\Dto\CreateGlobalApplicationCommandParams;
use App\Entity\Discord\Api\Endpoint\CreateGlobalApplicationCommandEndpoint;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\Entity\Discord\Api\Endpoint\CreateGlobalApplicationCommandEndpoint
 */
final class CreateGlobalApplicationCommandEndpointTest extends KernelTestCase
{
    /**
     * @return array[]
     */
    public static function provider_params(): array
    {
        return [[new CreateGlobalApplicationCommandParams(name: 'test-name')]];
    }

    /**
     * @return void
     */
    public function test_getRequestMethod(): void
    {
        self::assertSame('POST', CreateGlobalApplicationCommandEndpoint::getRequestMethod());
    }

    /**
     * @param CreateGlobalApplicationCommandParams $params
     * @return void
     * @dataProvider provider_params
     */
    public function test_getRequestBody(CreateGlobalApplicationCommandParams $params): void
    {
        self::assertSame(
            $params,
            (new CreateGlobalApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                params: $params
            ))->getRequestBody()
        );
    }

    /**
     * @param CreateGlobalApplicationCommandParams $params
     * @return void
     * @dataProvider provider_params
     */
    public function test_getRequestPath(CreateGlobalApplicationCommandParams $params): void
    {
        self::assertSame(
            'applications/test-application-id/commands',
            (new CreateGlobalApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                params: $params
            ))->getRequestPath()
        );
    }

    /**
     * @param CreateGlobalApplicationCommandParams $params
     * @return void
     * @dataProvider provider_params
     */
    public function test_getResponseBodyType(CreateGlobalApplicationCommandParams $params): void
    {
        self::assertSame(
            ApplicationCommand::class,
            (new CreateGlobalApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                params: $params
            ))->getResponseBodyType()
        );
    }

    /**
     * @param CreateGlobalApplicationCommandParams $params
     * @return void
     * @dataProvider provider_params
     */
    public function test_hasRequestBody(CreateGlobalApplicationCommandParams $params): void
    {
        self::assertTrue(
            (new CreateGlobalApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                params: $params
            ))->hasRequestBody()
        );
    }
}
