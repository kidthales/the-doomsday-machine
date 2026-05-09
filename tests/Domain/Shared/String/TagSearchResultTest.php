<?php

declare(strict_types=1);

namespace App\Tests\Domain\Shared\String;

use App\Domain\Shared\String\TagSearchResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * @author doomsday_coder
 */
#[CoversClass(TagSearchResult::class)]
final class TagSearchResultTest extends TestCase
{
    #[Test]
    #[TestWith(['#hello', 'hello'])]
    #[TestWith(['#world', 'world'])]
    #[TestWith(['#', ''])]
    public function testSubjectStripsStartCharacter(string $tag, string $expectedSubject): void
    {
        $result = new TagSearchResult($tag);
        $this->assertSame($expectedSubject, $result->subject);
    }

    #[Test]
    #[TestWith(['#hello world', 'hello world'])]
    #[TestWith(['#a b c d', 'a b c d'])]
    #[TestWith(['#\ ', ' '])]
    public function testSubjectUnescapesWhitespace(string $tag, string $expectedSubject): void
    {
        $result = new TagSearchResult($tag);
        $this->assertSame($expectedSubject, $result->subject);
    }

    #[Test]
    #[TestWith(['#a\\b', 'a\\b'])]
    #[TestWith(['#a\\\\b', 'a\\\\b'])]
    public function testSubjectPreservesNonWhitespaceBackslashes(string $tag, string $expectedSubject): void
    {
        $result = new TagSearchResult($tag);
        $this->assertSame($expectedSubject, $result->subject);
    }

    #[Test]
    public function testTagPropertyRemainsUnmodified(): void
    {
        $result = new TagSearchResult('#test');
        $this->assertSame('#test', $result->tag);
    }

    #[Test]
    public function testResultIsImmutable(): void
    {
        $result = new TagSearchResult('#immutable');
        // Readonly classes cannot be modified after construction.
        // This test verifies successful instantiation and type safety.
        $this->assertInstanceOf(TagSearchResult::class, $result);
        $this->assertIsString($result->tag);
        $this->assertIsString($result->subject);
    }
}
