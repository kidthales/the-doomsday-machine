<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\Jabronibetz\Repository;

use App\Domain\Jabronibetz\Repository\FootballOrganizationRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('jabronibetz')]
#[CoversClass(FootballOrganizationRepository::class)]
final class FootballOrganizationRepositoryTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has(FootballOrganizationRepository::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            FootballOrganizationRepository::class,
            $this->getContainer()->get(FootballOrganizationRepository::class),
            'The service must resolve to a valid FootballOrganizationRepository instance.'
        );
    }
}
