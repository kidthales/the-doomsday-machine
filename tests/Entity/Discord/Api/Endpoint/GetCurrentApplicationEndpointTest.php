<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Endpoint;

use App\Entity\Discord\Api\Dto\Application;
use App\Entity\Discord\Api\Endpoint\GetCurrentApplicationEndpoint;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\Entity\Discord\Api\Endpoint\GetCurrentApplicationEndpoint
 */
final class GetCurrentApplicationEndpointTest extends KernelTestCase
{
    /**
     * @return void
     */
    public function test_getRequestMethod(): void
    {
        self::assertSame('GET', GetCurrentApplicationEndpoint::getRequestMethod());
    }

    /**
     * @return void
     */
    public function test_getRequestBody(): void
    {
        self::assertNull((new GetCurrentApplicationEndpoint())->getRequestBody());
    }

    /**
     * @return void
     */
    public function test_getRequestPath(): void
    {
        self::assertSame('applications/@me', (new GetCurrentApplicationEndpoint())->getRequestPath());
    }

    /**
     * @return void
     */
    public function test_getResponseBodyType(): void
    {
        self::assertSame(Application::class, (new GetCurrentApplicationEndpoint())->getResponseBodyType());
    }

    /**
     * @return void
     */
    public function test_hasRequestBody(): void
    {
        self::assertFalse((new GetCurrentApplicationEndpoint())->hasRequestBody());
    }
}
