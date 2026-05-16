<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\AI\Console\AgentCall;

use App\Domain\AI\Console\AgentCall\UserInputProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('ai')]
#[CoversClass(UserInputProcessor::class)]
final class UserInputProcessorTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has(UserInputProcessor::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            UserInputProcessor::class,
            $this->getContainer()->get(UserInputProcessor::class),
            'The service must resolve to a valid UserInputProcessor instance.'
        );
    }
}
