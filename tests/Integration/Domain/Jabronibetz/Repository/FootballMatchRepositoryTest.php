<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\Jabronibetz\Repository;

use App\Domain\Jabronibetz\Repository\FootballMatchRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('jabronibetz')]
#[CoversClass(FootballMatchRepository::class)]
final class FootballMatchRepositoryTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has(FootballMatchRepository::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            FootballMatchRepository::class,
            $this->getContainer()->get(FootballMatchRepository::class),
            'The service must resolve to a valid FootballMatchRepository instance.'
        );
    }
}
