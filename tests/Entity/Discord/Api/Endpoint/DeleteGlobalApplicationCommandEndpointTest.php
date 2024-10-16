<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Endpoint;

use App\Entity\Discord\Api\Endpoint\DeleteGlobalApplicationCommandEndpoint;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\Entity\Discord\Api\Endpoint\DeleteGlobalApplicationCommandEndpoint
 */
final class DeleteGlobalApplicationCommandEndpointTest extends KernelTestCase
{
    /**
     * @return void
     */
    public function test_getRequestMethod(): void
    {
        self::assertSame('DELETE', DeleteGlobalApplicationCommandEndpoint::getRequestMethod());
    }

    /**
     * @return void
     */
    public function test_getRequestBody(): void
    {
        self::assertNull(
            (new DeleteGlobalApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                commandId: 'test-command-id',
            ))->getRequestBody()
        );
    }

    /**
     * @return void
     */
    public function test_getRequestPath(): void
    {
        self::assertSame(
            'applications/test-application-id/commands/test-command-id',
            (new DeleteGlobalApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                commandId: 'test-command-id',
            ))->getRequestPath()
        );
    }

    /**
     * @return void
     */
    public function test_getResponseBodyType(): void
    {
        self::assertNull(
            (new DeleteGlobalApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                commandId: 'test-command-id',
            ))->getResponseBodyType()
        );
    }

    /**
     * @return void
     */
    public function test_hasRequestBody(): void
    {
        self::assertFalse(
            (new DeleteGlobalApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                commandId: 'test-command-id',
            ))->hasRequestBody()
        );
    }
}
