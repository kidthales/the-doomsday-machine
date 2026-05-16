<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\AI\Console\AgentCall\UserInput;

use App\Domain\AI\Console\AgentCall\UserInput\ErrorUserInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('ai')]
#[CoversClass(ErrorUserInput::class)]
final class ErrorUserInputTest extends TestCase
{
    #[Test]
    public function it_instantiates(): void
    {
        $this->assertSame('Hello World!', (new ErrorUserInput('Hello World!'))->message);
    }
}
