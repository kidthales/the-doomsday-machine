<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared\AI;

use App\Domain\Shared\AI\PlatformResultProcessor;
use App\Domain\Shared\AI\PlatformStreamResultProcessor;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Metadata\Metadata;
use Symfony\AI\Platform\Result\RawResultInterface;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingDelta;
use Symfony\AI\Platform\Result\StreamResult;
use Symfony\AI\Platform\Result\TextResult;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('shared')]
#[CoversClass(PlatformResultProcessor::class)]
final class PlatformResultProcessorTest extends TestCase
{
    #[Test]
    public function it_processes_result_with_no_callbacks(): void
    {
        $callCount = 0;
        $gen = function () use (&$callCount) {
            ++$callCount;
            yield new ThinkingDelta('Hello');
            ++$callCount;
            yield new TextDelta('World');
        };

        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))->process(new StreamResult($gen()));

        $this->assertSame(2, $callCount);
    }

    #[Test]
    public function it_invokes_text_result_processor_callback(): void
    {
        $isCalled = false;
        $capturedResult = null;

        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))
            ->process(new TextResult('Hello World'), function ($result) use (&$isCalled, &$capturedResult) {
                $isCalled = true;
                $capturedResult = $result;
            });

        $this->assertTrue($isCalled);
        $this->assertInstanceOf(TextResult::class, $capturedResult);
        $this->assertSame('Hello World', $capturedResult->getContent());
    }

    #[Test]
    public function it_invokes_on_stream_result_start_callback(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $isCalled = false;
        $capturedResult = null;

        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))
            ->process(
                new StreamResult($gen()),
                onStreamResultStart: function ($result) use (&$isCalled, &$capturedResult) {
                    $isCalled = true;
                    $capturedResult = $result;
                }
            );

        $this->assertTrue($isCalled);
        $this->assertInstanceOf(StreamResult::class, $capturedResult);
    }

    #[Test]
    public function it_invokes_on_stream_result_finish_callback(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $isCalled = false;
        $capturedResult = null;

        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))
            ->process(
                new StreamResult($gen()),
                onStreamResultFinish: function ($result) use (&$isCalled, &$capturedResult) {
                    $isCalled = true;
                    $capturedResult = $result;
                }
            );

        $this->assertTrue($isCalled);
        $this->assertInstanceOf(StreamResult::class, $capturedResult);
    }

    #[Test]
    public function it_invokes_thinking_delta_processor_callback(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $isCalled = false;
        $capturedDelta = null;

        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))
            ->process(
                new StreamResult($gen()),
                thinkingDeltaProcessor: function ($delta) use (&$isCalled, &$capturedDelta) {
                    $isCalled = true;
                    $capturedDelta = $delta;
                }
            );

        $this->assertTrue($isCalled);
        $this->assertInstanceOf(ThinkingDelta::class, $capturedDelta);
        $this->assertSame('Hello', $capturedDelta->getThinking());
    }

    #[Test]
    public function it_invokes_text_delta_processor_callback(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $isCalled = false;
        $capturedDelta = null;

        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))
            ->process(
                new StreamResult($gen()),
                textDeltaProcessor: function ($delta) use (&$isCalled, &$capturedDelta) {
                    $isCalled = true;
                    $capturedDelta = $delta;
                }
            );

        $this->assertTrue($isCalled);
        $this->assertInstanceOf(TextDelta::class, $capturedDelta);
        $this->assertSame('World', $capturedDelta->getText());
    }

    #[Test]
    public function it_throws_exception_with_unsupported_result_interface_implementation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Unexpected result type/');

        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))->process(new class implements ResultInterface {
            public function getMetadata(): Metadata
            {
                return new Metadata();
            }
            public function getContent(): string|iterable|object|null
            {
                return 'Hello World';
            }
            public function getRawResult(): ?RawResultInterface
            {
                return null;
            }
            public function setRawResult(RawResultInterface $rawResult): void
            {
            }
        });
    }
}
