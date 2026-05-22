<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Jabronibetz\Entity;

use App\Domain\Jabronibetz\Entity\FootballTeam;
use App\Domain\Jabronibetz\Entity\FootballOrganization;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('jabronibetz')]
#[CoversClass(FootballTeam::class)]
final class FootballTeamTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $team = new FootballTeam();
        $this->assertNull($team->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_name(): void
    {
        $team = new FootballTeam();
        $this->assertNull($team->getName());
        $this->assertSame($team, $team->setName('Test Team'));
        $this->assertSame('Test Team', $team->getName());
    }

    #[Test]
    public function it_has_getter_and_setter_for_short_name(): void
    {
        $team = new FootballTeam();
        $this->assertNull($team->getShortName());
        $this->assertSame($team, $team->setShortName('TT'));
        $this->assertSame('TT', $team->getShortName());
    }

    #[Test]
    public function it_has_getter_and_setter_for_managing_organization(): void
    {
        $team = new FootballTeam();
        $this->assertNull($team->getManagingOrganization());
        $org = new FootballOrganization();
        $this->assertSame($team, $team->setManagingOrganization($org));
        $this->assertSame($org, $team->getManagingOrganization());
    }
}
