<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\Shared\Formatter;

use NumberFormatter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('shared')]
final class OrdinalNumberFormatterTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has('app.shared.formatter.ordinal_number_formatter'),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            NumberFormatter::class,
            $this->getContainer()->get('app.shared.formatter.ordinal_number_formatter'),
            'The service must resolve to a valid NumberFormatter instance.'
        );
    }
}
