<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared\AI;

use App\Domain\Shared\AI\PlatformStreamResultProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\AI\Platform\Result\Stream\Delta\DeltaInterface;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingDelta;
use Symfony\AI\Platform\Result\StreamResult;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('shared')]
#[CoversClass(PlatformStreamResultProcessor::class)]
final class PlatformStreamResultProcessorTest extends TestCase
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

        (new PlatformStreamResultProcessor())->process(new StreamResult($gen()));

        $this->assertSame(2, $callCount);
    }

    #[Test]
    public function it_invokes_on_start_callback(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $isCalled = false;
        $capturedResult = null;

        (new PlatformStreamResultProcessor())->process(
            new StreamResult($gen()),
            onStart: function ($result) use (&$isCalled, &$capturedResult) {
                $isCalled = true;
                $capturedResult = $result;
            }
        );

        $this->assertTrue($isCalled);
        $this->assertInstanceOf(StreamResult::class, $capturedResult);
    }

    #[Test]
    public function it_invokes_on_finish_callback(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $isCalled = false;
        $capturedResult = null;

        (new PlatformStreamResultProcessor())->process(
            new StreamResult($gen()),
            onFinish: function ($result) use (&$isCalled, &$capturedResult) {
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

        (new PlatformStreamResultProcessor())->process(
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

        (new PlatformStreamResultProcessor())->process(
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
    public function it_throws_exception_with_unsupported_delta_interface_implementation(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Unexpected stream result content delta type/');

        $gen = function () {
            yield new class implements DeltaInterface {
                public function getType(): string
                {
                    return 'unknown';
                }

                public function toArray(): array
                {
                    return [];
                }
            };
        };

        (new PlatformStreamResultProcessor())->process(new StreamResult($gen()));
    }

    #[Test]
    public function it_handles_empty_stream_gracefully(): void
    {
        $gen = function () {
            yield from [];
        };
        $isCalled = false;

        (new PlatformStreamResultProcessor())->process(
            new StreamResult($gen()),
            onFinish: function () use (&$isCalled) {
                $isCalled = true;
            }
        );

        $this->assertTrue($isCalled);
    }
}
