<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use EQS\HealthCheckProvider\DTO\CheckDetails;
use EQS\HealthCheckProvider\DTO\HealthResponse;
use EQS\HealthCheckProvider\HealthChecker\DoctrineConnectionHealthChecker;
use EQS\HealthCheckProvider\HealthChecker\HttpHealthChecker;
use EQS\HealthCheckProvider\RequestHandler;
use GuzzleHttp\Psr7\HttpFactory;
use JsonException;
use Psr\Http\Client\ClientInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HealthCheckController extends AbstractController
{
    /**
     * @param Connection $readerConnection
     * @param Connection $writerConnection
     * @param Connection $migratorConnection
     * @param ClientInterface $httpClient
     */
    public function __construct(
        #[Autowire(service: 'doctrine.dbal.reader_connection')] private readonly Connection   $readerConnection,
        #[Autowire(service: 'doctrine.dbal.writer_connection')] private readonly Connection   $writerConnection,
        #[Autowire(service: 'doctrine.dbal.migrator_connection')] private readonly Connection $migratorConnection,
        private readonly ClientInterface                                                      $httpClient
    )
    {
    }

    /**
     * @param Request $request
     * @return Response
     * @throws JsonException
     */
    #[
        Route(path: '/healthcheck', methods: ['GET']),
        Route(path: '/healthCheck', methods: ['GET']),
        Route(path: '/health_check', methods: ['GET']),
        Route(path: '/health-check', name: 'app_health_check', methods: ['GET'])
    ]
    public function __invoke(Request $request): Response
    {
        $psr17Factory = new HttpFactory();
        $psrBridge = new HttpFoundationFactory();

        $handler = new RequestHandler(
            new HealthResponse(),
            [
                new DoctrineConnectionHealthChecker(
                    new CheckDetails('DatabaseReaderConnection', true),
                    $this->readerConnection
                ),
                new DoctrineConnectionHealthChecker(
                    new CheckDetails('DatabaseWriterConnection', true),
                    $this->writerConnection
                ),
                new DoctrineConnectionHealthChecker(
                    new CheckDetails('DatabaseMigratorConnection', true),
                    $this->migratorConnection
                ),
                new HttpHealthChecker(
                    new CheckDetails('DiscordHttp', true),
                    $this->httpClient,
                    new \GuzzleHttp\Psr7\Request('GET', 'https://discord.com')
                )
            ],
            $psr17Factory,
            $psr17Factory
        );

        $psrFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        return $psrBridge->createResponse($handler->handle($psrFactory->createRequest($request)));
    }
}
