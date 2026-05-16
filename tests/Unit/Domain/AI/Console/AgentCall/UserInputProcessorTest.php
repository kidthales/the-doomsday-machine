<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\AI\Console\AgentCall;

use App\Domain\AI\Console\AgentCall\UserInput\ChatUserInput;
use App\Domain\AI\Console\AgentCall\UserInput\ClearUserInput;
use App\Domain\AI\Console\AgentCall\UserInput\ErrorUserInput;
use App\Domain\AI\Console\AgentCall\UserInput\ExitUserInput;
use App\Domain\AI\Console\AgentCall\UserInput\HelpUserInput;
use App\Domain\AI\Console\AgentCall\UserInput\NoopUserInput;
use App\Domain\AI\Console\AgentCall\UserInputProcessor;
use App\Domain\Shared\String\TagSearch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('ai')]
#[CoversClass(UserInputProcessor::class)]
final class UserInputProcessorTest extends TestCase
{
    #[Test]
    #[TestWith([null, NoopUserInput::class], 'null')]
    #[TestWith(['', NoopUserInput::class], "''")]
    #[TestWith(['   ', NoopUserInput::class], "'   '")]
    #[TestWith(['Test', ChatUserInput::class], "'Test'")]
    public function it_processes_basic_user_input(mixed $rawUserInput, string $expected): void
    {
        $this->assertInstanceOf(
            $expected,
            (new UserInputProcessor(new TagSearch(), 'test'))->process($rawUserInput, $this->createStub(SymfonyStyle::class))
        );
    }

    #[Test]
    #[TestWith(['/exit', ExitUserInput::class], "'/exit'")]
    #[TestWith(['/quit', ExitUserInput::class], "'/quit'")]
    #[TestWith(['/bye', ExitUserInput::class], "'/bye'")]
    #[TestWith(['/clear', ClearUserInput::class], "'/clear'")]
    #[TestWith(['/help', HelpUserInput::class], "'/help'")]
    #[TestWith(['/failit', ErrorUserInput::class], "'/failit'")]
    public function it_processes_basic_slash_commands(string $rawUserInput, string $expected): void
    {
        $this->assertInstanceOf(
            $expected,
            (new UserInputProcessor(new TagSearch(), 'test'))->process($rawUserInput, $this->createStub(SymfonyStyle::class))
        );
    }
}
