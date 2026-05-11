<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\Shared\Formatter;

use NumberFormatter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 */
#[Group('shared')]
final class OrdinalNumberFormatterTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->assertTrue(
            $container->has('app.shared.formatter.ordinal_number_formatter'),
            'The autowired service must be registered in the container.'
        );

        $formatter = $container->get('app.shared.formatter.ordinal_number_formatter');
        $this->assertInstanceOf(
            NumberFormatter::class,
            $formatter,
            'The service must resolve to a valid NumberFormatter instance.'
        );
    }
}
