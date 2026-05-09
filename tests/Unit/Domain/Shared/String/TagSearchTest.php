<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared\String;

use App\Domain\Shared\String\TagSearch;
use App\Domain\Shared\String\TagSearchResult;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * @author doomsday_coder
 */
#[CoversClass(TagSearch::class)]
final class TagSearchTest extends TestCase
{
    #[Test]
    #[TestWith(['#'])]
    #[TestWith(['@'])]
    #[TestWith(['!'])]
    public function testValidStartCharacter(string $start): void
    {
        $search = new TagSearch();
        $this->assertSame([], $search->search('no tags here', $start));
    }

    #[Test]
    #[TestWith([''])]
    #[TestWith([' '])]
    #[TestWith(['ab'])]
    public function testInvalidStartCharacterThrowsException(string $start): void
    {
        $search = new TagSearch();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tag start string must have exactly 1 non-whitespace character');
        $search->search('test', $start);
    }

    #[Test]
    #[TestWith(['hello #world', '#', ['world']])]
    #[TestWith(['#start', '#', ['start']])]
    #[TestWith(['#a', '#', ['a']])]
    #[TestWith(['#a b c', '#', ['a']])]
    #[TestWith(['foo #bar baz', '#', ['bar']])]
    #[TestWith(['#a #b #c', '#', ['a', 'b', 'c']])]
    #[TestWith(['#a\  #b', '#', ['a ', 'b']])]
    #[TestWith(['#', '#', []])]
    #[TestWith(['# ', '#', []])]
    public function testSearchParsesTagsCorrectly(string $subject, string $start, array $expectedSubjects): void
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
    public function testSearchHandlesMultipleSpacesInTag(): void
    {
        $search = new TagSearch();
        $results = $search->search('#a\ \ b', '#');
        $this->assertCount(1, $results);
        $this->assertSame('a  b', $results[0]->subject);
    }

    #[Test]
    public function testSearchIgnoresStartCharInsideWord(): void
    {
        $search = new TagSearch();
        $results = $search->search('hashtag#tag', '#');
        $this->assertSame([], $results);
    }

    #[Test]
    public function testSearchResetsOnWhitespaceAfterStartChar(): void
    {
        $search = new TagSearch();
        // '# ' should discard the tag because the character immediately after '#' is whitespace
        $results = $search->search('# rest', '#');
        $this->assertSame([], $results);
    }
}
