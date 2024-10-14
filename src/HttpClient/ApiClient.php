<?php

namespace App\HttpClient;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiClient
{
    /**
     * @param ApiEndpointInterface $endpoint
     * @return array
     */
    private static function getRequestOptions(ApiEndpointInterface $endpoint): array
    {
        $options = [];

        $requestHeaders = $endpoint->getRequestHeaders();

        if ($requestHeaders) {
            $options['headers'] = $requestHeaders;
        }

        $requestQueryParameters = $endpoint->getRequestQueryParameters();

        if ($requestQueryParameters) {
            $options['query'] = $requestQueryParameters;
        }

        if ($endpoint->hasRequestBody()) {
            self::assignRequestOptionsContent($options, $endpoint);
        }

        return $options;
    }

    /**
     * @param array $options
     * @param ApiEndpointInterface $endpoint
     * @return void
     */
    private static function assignRequestOptionsContent(array &$options, ApiEndpointInterface $endpoint): void
    {
        $requestContentType = $endpoint->getRequestContentType();

        $key = $requestContentType === 'application/json' ? 'json' : 'body';

        $options[$key] = $endpoint->getRequestBody();

        if (in_array($requestContentType, ['application/json', 'application/x-www-form-urlencoded'])) {
            return;
        }

        if (!isset($options['headers'])) {
            $options['headers'] = [];
        }

        $options['headers']['Content-Type'] = $requestContentType;
    }

    /**
     * @param HttpClientInterface $httpClient
     * @param SerializerInterface $serializer
     */
    public function __construct(protected HttpClientInterface $httpClient, protected SerializerInterface $serializer)
    {
    }

    /**
     * @param ApiEndpointInterface $endpoint
     * @param int|null $responseStatusCode
     * @param array|null $responseHeaders
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function request(
        ApiEndpointInterface $endpoint,
        ?int &$responseStatusCode = null,
        ?array &$responseHeaders = null
    ): mixed
    {
        $endpointClass = get_class($endpoint);

        $response = $this->httpClient->request(
            $endpointClass::getRequestMethod(),
            $this->getEndpointRequestPath($endpoint),
            self::getRequestOptions($endpoint)
        );

        $responseStatusCode = $response->getStatusCode();
        $responseHeaders = $response->getHeaders();

        $responseBodyType = $endpoint->getResponseBodyType();
        $responseContentType = $responseHeaders['content-type'][0] ?? null;

        if ($responseContentType === null) {
            return null;
        }

        if (str_contains($responseContentType, 'json')) {
            return $responseBodyType === null
                ? json_decode($response->getContent(), true)
                : $this->serializer->deserialize($response->getContent(), $endpoint->getResponseBodyType(),'json');
        }

        return $responseBodyType === null ? null : $response->getContent();
    }

    /**
     * @param ApiEndpointInterface $endpoint
     * @return string
     */
    protected function getEndpointRequestPath(ApiEndpointInterface $endpoint): string
    {
        return $endpoint->getRequestPath();
    }
}
