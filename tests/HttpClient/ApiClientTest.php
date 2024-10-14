<?php

namespace App\Tests\HttpClient;

use App\HttpClient\AbstractApiEndpoint;
use App\HttpClient\AbstractJsonApiEndpoint;
use App\HttpClient\ApiClient;
use App\HttpClient\ApiEndpointInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @covers \App\HttpClient\AbstractApiEndpoint
 * @covers \App\HttpClient\AbstractJsonApiEndpoint
 * @covers \App\HttpClient\ApiClient
 */
final class ApiClientTest extends KernelTestCase
{
    /**
     * @return array
     */
    public static function provider_request(): array
    {
        return [
            [
                new class extends AbstractJsonApiEndpoint {
                    public static function getRequestMethod(): string
                    {
                        return 'GET';
                    }

                    public function getRequestBody(): mixed
                    {
                        return null;
                    }

                    public function getRequestPath(): string
                    {
                        return 'https://httpstat.us/204';
                    }

                    public function getResponseBodyType(): ?string
                    {
                        return null;
                    }

                    public function hasRequestBody(): bool
                    {
                        return false;
                    }
                },
                null,
                204
            ],
            [
                new class extends AbstractJsonApiEndpoint {
                    public static function getRequestMethod(): string
                    {
                        return 'GET';
                    }

                    public function getRequestBody(): mixed
                    {
                        return null;
                    }

                    public function getRequestPath(): string
                    {
                        return 'https://httpstat.us/200';
                    }

                    public function getRequestQueryParameters(): array
                    {
                        return ['sleep' => 500];
                    }

                    public function getResponseBodyType(): ?string
                    {
                        return null;
                    }

                    public function hasRequestBody(): bool
                    {
                        return false;
                    }
                },
                null,
                200
            ],
            [
                new class extends AbstractJsonApiEndpoint {
                    public static function getRequestMethod(): string
                    {
                        return 'GET';
                    }

                    public function getRequestBody(): mixed
                    {
                        return null;
                    }

                    public function getRequestHeaders(): array
                    {
                        return ['Accept' => 'text/plain'];
                    }

                    public function getRequestPath(): string
                    {
                        return 'https://httpstat.us/200';
                    }

                    public function getRequestQueryParameters(): array
                    {
                        return ['sleep' => 500];
                    }

                    public function getResponseBodyType(): ?string
                    {
                        return null;
                    }

                    public function hasRequestBody(): bool
                    {
                        return false;
                    }
                },
                null,
                200
            ],
            [
                new class extends AbstractJsonApiEndpoint {
                    public static function getRequestMethod(): string
                    {
                        return 'GET';
                    }

                    public function getRequestBody(): mixed
                    {
                        return null;
                    }

                    public function getRequestHeaders(): array
                    {
                        return ['Accept' => 'text/plain'];
                    }

                    public function getRequestPath(): string
                    {
                        return 'https://httpstat.us/200';
                    }

                    public function getResponseBodyType(): ?string
                    {
                        return null;
                    }

                    public function hasRequestBody(): bool
                    {
                        return false;
                    }
                },
                null,
                200
            ],
            [
                new class extends AbstractJsonApiEndpoint {
                    public static function getRequestMethod(): string
                    {
                        return 'POST';
                    }

                    public function getRequestBody(): mixed
                    {
                        return ['title' => 'test-title', 'body' => 'test-body', 'userId' => 1];
                    }

                    public function getRequestHeaders(): array
                    {
                        return ['Accept' => 'application/json'];
                    }

                    public function getRequestPath(): string
                    {
                        return 'https://jsonplaceholder.typicode.com/posts';
                    }

                    public function getResponseBodyType(): ?string
                    {
                        return null;
                    }

                    public function hasRequestBody(): bool
                    {
                        return true;
                    }
                },
                ['title' => 'test-title', 'body' => 'test-body', 'userId' => 1, 'id' => 101],
                201
            ],
            [
                new class extends AbstractJsonApiEndpoint {
                    public static function getRequestMethod(): string
                    {
                        return 'POST';
                    }

                    public function getRequestBody(): mixed
                    {
                        return ['title' => 'test-title', 'body' => 'test-body', 'userId' => 1];
                    }

                    public function getRequestPath(): string
                    {
                        return 'https://jsonplaceholder.typicode.com/posts';
                    }

                    public function getRequestQueryParameters(): array
                    {
                        return ['sleep' => 500];
                    }

                    public function getResponseBodyType(): ?string
                    {
                        return null;
                    }

                    public function hasRequestBody(): bool
                    {
                        return true;
                    }
                },
                ['title' => 'test-title', 'body' => 'test-body', 'userId' => 1, 'id' => 101],
                201
            ],
            [
                new class extends AbstractApiEndpoint {
                    public static function getRequestMethod(): string
                    {
                        return 'POST';
                    }

                    public function getRequestBody(): mixed
                    {
                        return ['test-field' => 'test-value'];
                    }

                    public function getRequestHeaders(): array
                    {
                        return ['Accept' => 'application/json'];
                    }

                    public function getRequestPath(): string
                    {
                        return 'https://httpstat.us/201';
                    }

                    public function getResponseBodyType(): ?string
                    {
                        return null;
                    }

                    public function hasRequestBody(): bool
                    {
                        return true;
                    }
                },
                ['code' => 201, 'description' => 'Created'],
                201
            ],
            [
                new class extends AbstractApiEndpoint {
                    public static function getRequestMethod(): string
                    {
                        return 'POST';
                    }

                    public function getRequestBody(): mixed
                    {
                        return ['test-field' => 'test-value'];
                    }

                    public function getRequestHeaders(): array
                    {
                        return ['Accept' => 'application/json'];
                    }

                    public function getRequestPath(): string
                    {
                        return 'https://httpstat.us/201';
                    }

                    public function getRequestQueryParameters(): array
                    {
                        return ['sleep' => 500];
                    }

                    public function getResponseBodyType(): ?string
                    {
                        return null;
                    }

                    public function hasRequestBody(): bool
                    {
                        return true;
                    }
                },
                ['code' => 201, 'description' => 'Created'],
                201
            ],
            [
                new class extends AbstractApiEndpoint {
                    public static function getRequestMethod(): string
                    {
                        return 'POST';
                    }

                    public function getRequestBody(): mixed
                    {
                        return ['test-field' => 'test-value'];
                    }

                    public function getRequestContentType(): string
                    {
                        return 'multipart/form-data';
                    }

                    public function getRequestPath(): string
                    {
                        return 'https://httpstat.us/204';
                    }

                    public function getResponseBodyType(): ?string
                    {
                        return null;
                    }

                    public function hasRequestBody(): bool
                    {
                        return true;
                    }
                },
                null,
                204
            ],
            [
                new class extends AbstractApiEndpoint {
                    public static function getRequestMethod(): string
                    {
                        return 'POST';
                    }

                    public function getRequestBody(): mixed
                    {
                        return ['test-field' => 'test-value'];
                    }

                    public function getRequestContentType(): string
                    {
                        return 'multipart/form-data';
                    }

                    public function getRequestHeaders(): array
                    {
                        return ['Accept' => 'text/plain'];
                    }

                    public function getRequestPath(): string
                    {
                        return 'https://httpstat.us/204';
                    }

                    public function getResponseBodyType(): ?string
                    {
                        return null;
                    }

                    public function hasRequestBody(): bool
                    {
                        return true;
                    }
                },
                null,
                204
            ],
            [
                new class extends AbstractJsonApiEndpoint {
                    public static function getRequestMethod(): string
                    {
                        return 'POST';
                    }

                    public function getRequestBody(): mixed
                    {
                        return new TestRequestBody('test-title', 'test-body', 1);
                    }

                    public function getRequestHeaders(): array
                    {
                        return ['Accept' => 'application/json'];
                    }

                    public function getRequestPath(): string
                    {
                        return 'https://jsonplaceholder.typicode.com/posts';
                    }

                    public function getResponseBodyType(): ?string
                    {
                        return TestResponseBody::class;
                    }

                    public function hasRequestBody(): bool
                    {
                        return true;
                    }
                },
                new TestResponseBody('test-title', 'test-body', 1, 101),
                201
            ],
            [
                new class extends AbstractApiEndpoint {
                    public static function getRequestMethod(): string
                    {
                        return 'GET';
                    }

                    public function getRequestBody(): mixed
                    {
                        return null;
                    }

                    public function getRequestContentType(): string
                    {
                        return 'text/plain';
                    }

                    public function getRequestPath(): string
                    {
                        return 'https://www.google.com';
                    }

                    public function getResponseBodyType(): ?string
                    {
                        return 'text/html';
                    }

                    public function hasRequestBody(): bool
                    {
                        return false;
                    }
                },
                '__html',
                200
            ]
        ];
    }

    /**
     * @param ApiEndpointInterface $endpoint
     * @param mixed $expected
     * @param int $expectedStatusCode
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @dataProvider provider_request
     */
    public function test_request(ApiEndpointInterface $endpoint, mixed $expected, int $expectedStatusCode): void
    {
        self::bootKernel();

        /** @var ApiClient $subject */
        $subject = self::getContainer()->get(ApiClient::class);

        $actualStatusCode = null;
        $actualHeaders = null;
        $actual = $subject->request($endpoint, $actualStatusCode, $actualHeaders);

        self::assertSame($expectedStatusCode, $actualStatusCode);
        self::assertIsArray($actualHeaders);
        self::assertNotEmpty($actualHeaders);

        if ($expected instanceof TestResponseBody) {
            self::assertInstanceOf(TestResponseBody::class, $actual);
            self::assertSame($expected->id, $actual->id);
            self::assertSame($expected->title, $actual->title);
            self::assertSame($expected->body, $actual->body);
            self::assertSame($expected->userId, $actual->userId);
        } else if ($expected === '__html') {
            self::assertIsString($actual);
            self::assertNotEmpty($actual);
        } else {
            self::assertSame($expected, $actual);
        }
    }
}

final class TestRequestBody {
    public function __construct(public string $title, public string $body, public int $userId)
    {
    }
}

final class TestResponseBody {
    public function __construct(public string $title, public string $body, public int $userId, public int $id)
    {
    }
}
