<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared\Formatter;

use App\Domain\Shared\Formatter\OrdinalNumberFormatterAwareTrait;
use NumberFormatter;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Lightweight subject to expose the trait's protected property for testing.
 * In larger codebases, extract this to `tests/Support/OrdinalNumberFormatterAwareTraitTestSubject.php`
 *
 * @author doomsday_coder
 */
final class OrdinalNumberFormatterAwareTraitTestSubject
{
    use OrdinalNumberFormatterAwareTrait;

    public function getOrdinalNumberFormatter(): NumberFormatter
    {
        return $this->ordinalNumberFormatter;
    }
}

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('shared')]
#[CoversTrait(OrdinalNumberFormatterAwareTrait::class)]
final class OrdinalNumberFormatterAwareTraitTest extends TestCase
{
    #[Test]
    public function it_assigns_formatter_to_property(): void
    {
        $subject = new OrdinalNumberFormatterAwareTraitTestSubject();
        $formatter = new NumberFormatter('en_US', NumberFormatter::ORDINAL);

        $subject->setOrdinalNumberFormatter($formatter);

        $this->assertSame($formatter, $subject->getOrdinalNumberFormatter());
    }

    #[Test]
    public function it_overwrites_existing_formatter(): void
    {
        $subject = new OrdinalNumberFormatterAwareTraitTestSubject();
        $formatterA = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
        $formatterB = new NumberFormatter('fr_FR', NumberFormatter::ORDINAL);

        $subject->setOrdinalNumberFormatter($formatterA);
        $subject->setOrdinalNumberFormatter($formatterB);

        $this->assertSame($formatterB, $subject->getOrdinalNumberFormatter());
    }

    #[Test]
    public function it_enforces_type_safety(): void
    {
        $subject = new OrdinalNumberFormatterAwareTraitTestSubject();
        $this->expectException(TypeError::class);
        $subject->setOrdinalNumberFormatter('invalid');
    }
}
