<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use NumberFormatter;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 */
final class OrdinalNumberFormatterDiTest extends KernelTestCase
{
    #[Test]
    public function container_provides_ordinal_number_formatter_service(): void
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
