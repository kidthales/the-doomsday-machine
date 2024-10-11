<?php

namespace App\Tests\Controller;

use App\Controller\HealthCheckController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\Controller\HealthCheckController
 */
final class HealthCheckControllerTest extends KernelTestCase
{
    /**
     * @return void
     */
    public function test(): void
    {
        self::bootKernel();

        /** @var HealthCheckController $subject */
        $subject = self::getContainer()->get(HealthCheckController::class);

        /** @var Response $response */
        $response = call_user_func($subject, Request::create('/'));

        $actual = json_decode($response->getContent(), true);

        self::assertSame('pass', $actual['status']);
        self::assertNotEmpty($actual['checks']);
    }
}
