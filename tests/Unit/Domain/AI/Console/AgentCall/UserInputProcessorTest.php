<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\AI\Console\AgentCall;

use App\Domain\AI\Console\AgentCall\UserInput\ExitUserInput;
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
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('ai')]
#[CoversClass(UserInputProcessor::class)]
final class UserInputProcessorTest extends TestCase
{
    #[Test]
    #[TestWith([null], 'null')]
    #[TestWith([''], "''")]
    #[TestWith(['   '], "'   '")]
    public function it_processes_user_input_as_noop_user_input(mixed $rawUserInput): void
    {
        $this->assertInstanceOf(
            NoopUserInput::class,
            (new UserInputProcessor(new TagSearch(), 'test'))->process($rawUserInput, $this->createStub(SymfonyStyle::class))
        );
    }

    #[Test]
    #[TestWith(['/exit'], "'/exit'")]
    #[TestWith(['/quit'], "'/quit'")]
    #[TestWith(['/bye'], "'/bye'")]
    public function it_processes_slash_command_as_exit_user_input(string $rawUserInput): void
    {
        $this->assertInstanceOf(
            ExitUserInput::class,
            (new UserInputProcessor(new TagSearch(), 'test'))->process($rawUserInput, $this->createStub(SymfonyStyle::class))
        );
    }
}
