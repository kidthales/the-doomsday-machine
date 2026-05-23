<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Jabronibetz\Entity;

use App\Domain\Jabronibetz\Entity\FootballGender;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('jabronibetz')]
#[CoversClass(FootballGender::class)]
final class FootballGenderTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $org = new FootballGender();
        $this->assertNull($org->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_name(): void
    {
        $org = new FootballGender();
        $this->assertNull($org->getName());
        $this->assertSame($org, $org->setName('Test'));
        $this->assertSame('Test', $org->getName());
    }
}
