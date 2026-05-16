<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\AI\Console\AgentCall\UserInput;

use App\Domain\AI\Console\AgentCall\UserInput\ChatUserInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Message\MessageBag;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('ai')]
#[CoversClass(ChatUserInput::class)]
final class ChatUserInputTest extends TestCase
{
    #[Test]
    public function it_instantiates(): void
    {
        $messageBag = new MessageBag();
        $this->assertSame($messageBag, (new ChatUserInput($messageBag))->messages);
    }
}
