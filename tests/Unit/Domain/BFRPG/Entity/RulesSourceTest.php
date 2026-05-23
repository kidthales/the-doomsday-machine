<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\BFRPG\Entity;

use App\Domain\BFRPG\Entity\RulesSource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('bfrpg')]
#[CoversClass(RulesSource::class)]
final class RulesSourceTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $source = new RulesSource();
        $this->assertNull($source->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_name(): void
    {
        $source = new RulesSource();
        $this->assertNull($source->getName());
        $this->assertSame($source, $source->setName('Test Source'));
        $this->assertSame('Test Source', $source->getName());
    }
}
