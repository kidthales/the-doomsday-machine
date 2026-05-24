<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Jabronibetz\Entity;

use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballCompetitionTeamEntry;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('jabronibetz')]
#[CoversClass(FootballCompetitionTeamEntry::class)]
final class FootballCompetitionTeamEntryTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $cmp = new FootballCompetitionTeamEntry();
        $this->assertNull($cmp->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_competition(): void
    {
        $entry = new FootballCompetitionTeamEntry();
        $this->assertNull($entry->getCompetition());
        $cmp = new FootballCompetition();
        $this->assertSame($entry, $entry->setCompetition($cmp));
        $this->assertSame($cmp, $entry->getCompetition());
    }

    #[Test]
    public function it_has_getter_and_setter_for_team(): void
    {
        $entry = new FootballCompetitionTeamEntry();
        $this->assertNull($entry->getTeam());
        $team = new FootballTeam();
        $this->assertSame($entry, $entry->setTeam($team));
        $this->assertSame($team, $entry->getTeam());
    }

    #[Test]
    public function it_has_getter_and_setter_for_group(): void
    {
        $entry = new FootballCompetitionTeamEntry();
        $this->assertNull($entry->getGroup());
        $group = 'T';
        $this->assertSame($entry, $entry->setGroup($group));
        $this->assertSame($group, $entry->getGroup());
    }

    #[Test]
    public function it_has_getter_and_setter_for_result(): void
    {
        $entry = new FootballCompetitionTeamEntry();
        $this->assertNull($entry->getResult());
        $result = 'Winners';
        $this->assertSame($entry, $entry->setResult($result));
        $this->assertSame($result, $entry->getResult());
    }
}
