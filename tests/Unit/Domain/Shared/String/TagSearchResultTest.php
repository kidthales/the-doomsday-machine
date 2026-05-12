<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared\String;

use App\Domain\Shared\String\TagSearchResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('shared')]
#[CoversClass(TagSearchResult::class)]
final class TagSearchResultTest extends TestCase
{
    #[Test]
    #[TestWith(['#hello', 'hello'], "'#hello' -> 'hello'")]
    #[TestWith(['#world', 'world'], "'#world' -> 'world'")]
    #[TestWith(['#', ''], "'#' -> ''")]
    public function it_strips_start_character(string $tag, string $expectedSubject): void
    {
        $result = new TagSearchResult($tag);
        $this->assertSame($expectedSubject, $result->subject);
    }

    #[Test]
    #[TestWith(['#hello world', 'hello world'], "'#hello world' -> 'hello world'")]
    #[TestWith(['#a b c d', 'a b c d'], "'#a b c d' -> 'a b c d''")]
    #[TestWith(['#\ ', ' '], "'#\ ' -> ' '")]
    public function it_unescapes_whitespace(string $tag, string $expectedSubject): void
    {
        $result = new TagSearchResult($tag);
        $this->assertSame($expectedSubject, $result->subject);
    }

    #[Test]
    #[TestWith(['#a\b', 'a\b'], "'#a\b' -> 'a\b'")]
    #[TestWith(['#a\\\b', 'a\\\b'], "'#a\\\b' -> 'a\\\b'")]
    public function it_preserves_non_whitespace_backslashes(string $tag, string $expectedSubject): void
    {
        $result = new TagSearchResult($tag);
        $this->assertSame($expectedSubject, $result->subject);
    }
}
