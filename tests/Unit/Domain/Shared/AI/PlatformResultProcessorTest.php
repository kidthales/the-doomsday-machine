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
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('shared')]
#[CoversClass(PlatformResultProcessor::class)]
final class PlatformResultProcessorTest extends TestCase
{
    #[Test]
    public function it_processes_result_with_no_callbacks(): void
    {
        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))->process(new TextResult('Hello World'));
        $this->assertTrue(true);
    }

    #[Test]
    public function it_processes_result_with_text_result_processor_callback(): void
    {
        $isCalled = false;
        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))
            ->process(new TextResult('Hello World'), function ($result) use (&$isCalled) {
                $isCalled = true;
                $this->assertInstanceOf(TextResult::class, $result);
                $this->assertSame('Hello World', $result->getContent());
            });

        $this->assertTrue($isCalled);
    }

    #[Test]
    public function it_processes_result_with_on_stream_result_start_callback(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $isCalled = false;
        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))
            ->process(new StreamResult($gen()), onStreamResultStart:  function ($result) use (&$isCalled) {
                $isCalled = true;
                $this->assertInstanceOf(StreamResult::class, $result);
            });

        $this->assertTrue($isCalled);
    }

    #[Test]
    public function it_processes_result_with_on_stream_result_finish_callback(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $isCalled = false;
        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))
            ->process(new StreamResult($gen()), onStreamResultFinish: function ($result) use (&$isCalled) {
            $isCalled = true;
            $this->assertInstanceOf(StreamResult::class, $result);
        });

        $this->assertTrue($isCalled);
    }

    #[Test]
    public function it_processes_result_with_thinking_delta_processor_callback(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $isCalled = false;
        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))
            ->process(new StreamResult($gen()), thinkingDeltaProcessor: function ($delta) use (&$isCalled) {
                $isCalled = true;
                $this->assertInstanceOf(ThinkingDelta::class, $delta);
                $this->assertSame('Hello', $delta->getThinking());
            });

        $this->assertTrue($isCalled);
    }

    #[Test]
    public function it_processes_result_with_text_delta_processor_callback(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $isCalled = false;
        (new PlatformResultProcessor(new PlatformStreamResultProcessor()))
            ->process(new StreamResult($gen()), textDeltaProcessor: function ($delta) use (&$isCalled) {
                $isCalled = true;
                $this->assertInstanceOf(TextDelta::class, $delta);
                $this->assertSame('World', $delta->getText());
            });

        $this->assertTrue($isCalled);
    }

    #[Test]
    public function it_throws_exception_with_unsupported_result_interface_implementation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unexpected result type');

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
