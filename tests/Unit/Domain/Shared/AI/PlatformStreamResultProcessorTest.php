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
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('shared')]
#[CoversClass(PlatformStreamResultProcessor::class)]
final class PlatformStreamResultProcessorTest extends TestCase
{
    #[Test]
    public function it_processes_result_with_no_callbacks(): void
    {
        $ix = 0;
        $gen = function () use (&$ix) {
            ++$ix;
            yield new ThinkingDelta('Hello');
            ++$ix;
            yield new TextDelta('World');
        };

        (new PlatformStreamResultProcessor())->process(new StreamResult($gen()));

        $this->assertSame(2, $ix);
    }

    #[Test]
    public function it_processes_result_with_on_start_callback(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $isCalled = false;
        (new PlatformStreamResultProcessor())->process(new StreamResult($gen()), function ($result) use (&$isCalled) {
            $isCalled = true;
            $this->assertInstanceOf(StreamResult::class, $result);
        });

        $this->assertTrue($isCalled);
    }

    #[Test]
    public function it_processes_result_with_on_finish_callback(): void
    {
        $gen = function () {
            yield new ThinkingDelta('Hello');
            yield new TextDelta('World');
        };

        $isCalled = false;
        (new PlatformStreamResultProcessor())->process(new StreamResult($gen()), onFinish: function ($result) use (&$isCalled) {
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
        (new PlatformStreamResultProcessor())->process(new StreamResult($gen()), thinkingDeltaProcessor: function ($delta) use (&$isCalled) {
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
        (new PlatformStreamResultProcessor())->process(new StreamResult($gen()), textDeltaProcessor: function ($delta) use (&$isCalled) {
            $isCalled = true;
            $this->assertInstanceOf(TextDelta::class, $delta);
            $this->assertSame('World', $delta->getText());
        });

        $this->assertTrue($isCalled);
    }

    #[Test]
    public function it_throws_exception_with_unsupported_delta_interface_implementation(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected stream result content delta type');

        $gen = function () {
            yield new class implements DeltaInterface {};
        };

        (new PlatformStreamResultProcessor())->process(new StreamResult($gen()));
    }
}
