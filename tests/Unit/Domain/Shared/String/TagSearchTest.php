<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared\String;

use App\Domain\Shared\String\TagSearch;
use App\Domain\Shared\String\TagSearchResult;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * @author doomsday_coder
 */
#[Group('shared')]
#[CoversClass(TagSearch::class)]
final class TagSearchTest extends TestCase
{
    #[Test]
    #[TestWith(['#'], "'#'")]
    #[TestWith(['@'], "'@'")]
    #[TestWith(['!'], "'!'")]
    public function valid_start_character(string $start): void
    {
        $search = new TagSearch();
        $this->assertSame([], $search->search('no tags here', $start));
    }

    #[Test]
    #[TestWith([''], "''")]
    #[TestWith([' '], "' '")]
    #[TestWith(['ab'], "'ab'")]
    public function invalid_start_character_throws_exception(string $start): void
    {
        $search = new TagSearch();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tag start string must have exactly 1 non-whitespace character');
        $search->search('test', $start);
    }

    #[Test]
    #[TestWith(['', '#', []], "'' -> ''")]
    #[TestWith(['#', '#', []], "'#' -> ''")]
    #[TestWith(['# ', '#', []], "'# ' -> ''")]
    #[TestWith(['#  ', '#', []], "'#  ' -> ''")]
    #[TestWith(['#\\', '#', ['\\']], "'#\\' -> '\\'")]
    #[TestWith(['#\ ', '#', [' ']], "'#\ ' -> ' '")]
    #[TestWith(['#\  ', '#', [' ']], "'#\  ' -> ' '")]
    #[TestWith(['#\\\\', '#', ['\\\\']], "'#\\\' -> '\\\'")]
    #[TestWith(['#\\\ ', '#', ['\ ']], "'#\\\ ' -> '\ '")]
    #[TestWith(['#\\\  ', '#', ['\ ']], "'#\\\  ' -> '\ '")]
    #[TestWith(['#a', '#', ['a']], "'#a' -> 'a'")]
    #[TestWith(['#a ', '#', ['a']], "'#a ' -> 'a'")]
    #[TestWith(['#aa', '#', ['aa']], "'#aa' -> 'aa'")]
    #[TestWith(['#aa ', '#', ['aa']], "'#a ' -> 'aa'")]
    #[TestWith(['#\a', '#', ['\a']], "'#\a' -> '\a'")]
    #[TestWith(['#\a ', '#', ['\a']], "'#\a ' -> '\a'")]
    #[TestWith(['#a\\', '#', ['a\\']], "'#a\' -> 'a\'")]
    #[TestWith(['#a\ ', '#', ['a ']], "'#a\ ' -> 'a '")]
    #[TestWith(['#a\  ', '#', ['a ']], "'#a\  ' -> 'a '")]
    #[TestWith(['#\ \\', '#', [' \\']], "'#\ \' -> ' \\'")]
    #[TestWith(['#\ \ ', '#', ['  ']], "'#\ \ ' -> '  '")]
    #[TestWith(['#\ \  ', '#', ['  ']], "'#\ \  ' -> '  '")]
    #[TestWith(['#\ \\\\', '#', [' \\\\']], "'#\ \\\' -> ' \\\'")]
    #[TestWith(['#\ \\\ ', '#', [' \ ']], "'#\ \\\ ' -> ' \ '")]
    #[TestWith(['#\ \\\  ', '#', [' \ ']], "'#\ \\\  ' -> ' \ '")]
    #[TestWith(['#\ a', '#', [' a']], "'#\ a' -> ' a'")]
    #[TestWith(['#\ a ', '#', [' a']], "'#\ a ' -> ' a'")]
    #[TestWith(['#\ a\ ', '#', [' a ']], "'#\ a\ ' -> ' a '")]
    #[TestWith(['#\ a\  ', '#', [' a ']], "'#\ a\  ' -> ' a '")]
    #[TestWith(['hello #world', '#', ['world']], "'hello #world' -> 'world'")]
    #[TestWith(['foo #bar baz', '#', ['bar']], "'foo #bar baz' -> 'bar'")]
    #[TestWith(['#a #b #c', '#', ['a', 'b', 'c']], "'#a #b #c' -> 'a' 'b' 'c'")]
    #[TestWith(['#a\  #b', '#', ['a ', 'b']], "'#a\  #b' -> 'a ', 'b'")]
    public function search_parses_tags_correctly(string $subject, string $start, array $expectedSubjects): void
    {
        $search = new TagSearch();
        $results = $search->search($subject, $start);

        $this->assertCount(count($expectedSubjects), $results);
        foreach ($results as $i => $result) {
            $this->assertInstanceOf(TagSearchResult::class, $result);
            $this->assertSame($expectedSubjects[$i], $result->subject);
        }
    }

    #[Test]
    public function search_handles_multiple_spaces_in_tag(): void
    {
        $search = new TagSearch();
        $results = $search->search('#a\ \ b', '#');
        $this->assertCount(1, $results);
        $this->assertSame('a  b', $results[0]->subject);
    }

    #[Test]
    public function search_ignores_start_char_inside_word(): void
    {
        $search = new TagSearch();
        $results = $search->search('hashtag#tag', '#');
        $this->assertSame([], $results);
    }

    #[Test]
    public function search_resets_on_whitespace_after_start_char(): void
    {
        $search = new TagSearch();
        // '# ' should discard the tag because the character immediately after '#' is whitespace
        $results = $search->search('# rest', '#');
        $this->assertSame([], $results);
    }
}
