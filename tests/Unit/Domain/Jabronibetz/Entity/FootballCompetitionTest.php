<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Jabronibetz\Entity;

use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballOrganization;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('jabronibetz')]
#[CoversClass(FootballCompetition::class)]
final class FootballCompetitionTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $cmp = new FootballCompetition();
        $this->assertNull($cmp->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_name(): void
    {
        $cmp = new FootballCompetition();
        $this->assertNull($cmp->getName());
        $this->assertSame($cmp, $cmp->setName('2026 Test Competition'));
        $this->assertSame('2026 Test Competition', $cmp->getName());
    }

    #[Test]
    public function it_has_getter_and_setter_for_short_name(): void
    {
        $cmp = new FootballCompetition();
        $this->assertNull($cmp->getShortName());
        $this->assertSame($cmp, $cmp->setShortName('TC26'));
        $this->assertSame('TC26', $cmp->getShortName());
    }

    #[Test]
    public function it_has_getter_and_setter_for_organization(): void
    {
        $cmp = new FootballCompetition();
        $this->assertNull($cmp->getOrganization());
        $org = new FootballOrganization();
        $this->assertSame($cmp, $cmp->setOrganization($org));
        $this->assertSame($org, $cmp->getOrganization());
    }
}
