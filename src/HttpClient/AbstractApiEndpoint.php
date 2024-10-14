<?php

declare(strict_types=1);

namespace App\HttpClient;

abstract class AbstractApiEndpoint implements ApiEndpointInterface
{
    /**
     * @inheritDoc
     */
    abstract public static function getRequestMethod(): string;

    /**
     * @inheritDoc
     */
    abstract public function getRequestBody(): mixed;

    /**
     * @inheritDoc
     */
    public function getRequestContentType(): string
    {
        return 'application/x-www-form-urlencoded';
    }

    /**
     * @inheritDoc
     */
    public function getRequestHeaders(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    abstract public function getRequestPath(): string;

    /**
     * @inheritDoc
     */
    public function getRequestQueryParameters(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    abstract public function getResponseBodyType(): ?string;

    /**
     * @inheritDoc
     */
    abstract public function hasRequestBody(): bool;
}
