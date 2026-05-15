<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\AI\Console;

use App\Domain\AI\Console\AgentCallPlatformResultProcessor;
use App\Domain\Shared\AI\PlatformResultProcessor;
use App\Domain\Shared\AI\PlatformStreamResultProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\AI\Platform\Message\AssistantMessage;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingDelta;
use Symfony\AI\Platform\Result\StreamResult;
use Symfony\AI\Platform\Result\TextResult;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('ai')]
#[CoversClass(AgentCallPlatformResultProcessor::class)]
final class AgentCallPlatformResultProcessorTest extends TestCase
{
    #[Test]
    public function it_processes_a_text_result(): void
    {
        $io = $this->createMock(SymfonyStyle::class);
        $messages = $this->createMock(MessageBag::class);

        $writelnCount = 0;
        $io->expects($this->exactly(2))
            ->method('writeln')
            ->with(
               $this->callback(function (string $msg) use (&$writelnCount) {
                   return match (++$writelnCount) {
                       1 => $msg === '<fg=yellow>Assistant</>:',
                       2 => $msg === 'Hello World'
                   };
               })
            );

        $io->expects($this->once())
            ->method('newLine');

        $messages->expects($this->once())
            ->method('add')
            ->with(
                $this->callback(fn ($msg) => $msg instanceof AssistantMessage && $msg->getContent() === 'Hello World')
            );

        (new AgentCallPlatformResultProcessor(new PlatformResultProcessor(new PlatformStreamResultProcessor())))
            ->process(new TextResult('Hello World'), $io, $messages);
    }

    #[Test]
    public function it_processes_a_stream_result_with_text_delta(): void
    {
        $gen = function () {
            yield new TextDelta('Hello World');
        };

        $io = $this->createMock(SymfonyStyle::class);
        $messages = $this->createMock(MessageBag::class);

        $io->expects($this->once())
            ->method('writeln')
            ->with($this->equalTo('<fg=yellow>Assistant</>:'));

        $io->expects($this->once())
            ->method('write')
            ->with($this->equalTo('Hello World'));

        $io->expects($this->once())
            ->method('newLine');

        $messages->expects($this->once())
            ->method('add')
            ->with($this->callback(fn ($msg) => $msg instanceof AssistantMessage && $msg->getContent() === 'Hello World'));

        (new AgentCallPlatformResultProcessor(new PlatformResultProcessor(new PlatformStreamResultProcessor())))
            ->process(new StreamResult($gen()), $io, $messages);
    }

    #[Test]
    public function it_toggles_thinking_state_while_processing_a_stream_result_with_thinking_delta(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello World');
        };

        $io = $this->createMock(SymfonyStyle::class);
        $messages = $this->createMock(MessageBag::class);

        $io->expects($this->once())
            ->method('writeln')
            ->with($this->equalTo('<fg=yellow>Assistant</>:'));

        $writeCount = 0;
        $io->expects($this->exactly(2))
            ->method('write')
            ->with(
                $this->callback(function (string $msg) use (&$writeCount) {
                    return match (++$writeCount) {
                        1 => $msg === '<fg=magenta>',
                        2 => $msg === 'Hello World'
                    };
                })
            );

        $io->expects($this->once())
            ->method('newLine');

        $messages->expects($this->once())
            ->method('add')
            ->with($this->callback(fn ($msg) => $msg instanceof AssistantMessage && $msg->getContent() === ''));

        (new AgentCallPlatformResultProcessor(new PlatformResultProcessor(new PlatformStreamResultProcessor())))
            ->process(new StreamResult($gen()), $io, $messages);
    }

    #[Test]
    public function it_handles_state_transition_while_processing_a_stream_result_with_thinking_delta_and_text_delta(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $io = $this->createMock(SymfonyStyle::class);
        $messages = $this->createMock(MessageBag::class);

        $io->expects($this->once())
            ->method('writeln')
            ->with($this->equalTo('<fg=yellow>Assistant</>:'));

        $writeCount = 0;
        $io->expects($this->exactly(4))
            ->method('write')
            ->with(
                $this->callback(function (string $msg) use (&$writeCount) {
                    return match (++$writeCount) {
                        1 => $msg === '<fg=magenta>',
                        2 => $msg === 'Hello',
                        3 => $msg === '</>',
                        4 => $msg === 'World'
                    };
                })
            );

        $io->expects($this->exactly(2))
            ->method('newLine');

        $messages->expects($this->once())
            ->method('add')
            ->with($this->callback(fn ($msg) => $msg instanceof AssistantMessage && $msg->getContent() === 'World'));

        (new AgentCallPlatformResultProcessor(new PlatformResultProcessor(new PlatformStreamResultProcessor())))
            ->process(new StreamResult($gen()), $io, $messages);
    }

    #[Test]
    public function it_resets_internal_state_after_processing(): void
    {
        $gen = function () {
            yield new TextDelta('Hello World');
        };

        $io = $this->createStub(SymfonyStyle::class);
        $messages = $this->createStub(MessageBag::class);

        $processor = new AgentCallPlatformResultProcessor(new PlatformResultProcessor(new PlatformStreamResultProcessor()));
        $processor->process(new StreamResult($gen()), $io, $messages);

        $reflection = new ReflectionClass($processor);

        $isThinkingProp = $reflection->getProperty('isThinking');
        $this->assertFalse($isThinkingProp->getValue($processor));

        $textBufferProp = $reflection->getProperty('textBuffer');
        $this->assertSame('', $textBufferProp->getValue($processor));
    }
}
