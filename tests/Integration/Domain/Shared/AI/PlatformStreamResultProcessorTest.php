<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\Shared\AI;

use App\Domain\Shared\AI\PlatformStreamResultProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('shared')]
#[CoversClass(PlatformStreamResultProcessor::class)]
final class PlatformStreamResultProcessorTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has(PlatformStreamResultProcessor::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            PlatformStreamResultProcessor::class,
            $this->getContainer()->get(PlatformStreamResultProcessor::class),
            'The service must resolve to a valid PlatformStreamResultProcessor instance.'
        );
    }
}
