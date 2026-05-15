<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\AI\Console;

use App\Domain\AI\Console\AgentCallPlatformResultProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('shared')]
#[CoversClass(AgentCallPlatformResultProcessor::class)]
final class AgentCallPlatformResultProcessorTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has(AgentCallPlatformResultProcessor::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            AgentCallPlatformResultProcessor::class,
            $this->getContainer()->get(AgentCallPlatformResultProcessor::class),
            'The service must resolve to a valid AgentCallPlatformResultProcessor instance.'
        );
    }
}
