<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Jabronibetz\Entity;

use App\Domain\Jabronibetz\Entity\FootballOrganization;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('jabronibetz')]
#[CoversClass(FootballOrganization::class)]
final class FootballOrganizationTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $org = new FootballOrganization();
        $this->assertNull($org->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_name(): void
    {
        $org = new FootballOrganization();
        $this->assertNull($org->getName());
        $this->assertSame($org, $org->setName('Test Association'));
        $this->assertSame('Test Association', $org->getName());
    }

    #[Test]
    public function it_has_getter_and_setter_for_short_name(): void
    {
        $org = new FootballOrganization();
        $this->assertNull($org->getShortName());
        $this->assertSame($org, $org->setShortName('TA'));
        $this->assertSame('TA', $org->getShortName());
    }
}
