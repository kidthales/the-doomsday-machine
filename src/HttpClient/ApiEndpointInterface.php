<?php

declare(strict_types=1);

namespace App\HttpClient;

interface ApiEndpointInterface
{
    /**
     * @return string
     */
    public static function getRequestMethod(): string;

    /**
     * @return mixed
     */
    public function getRequestBody(): mixed;

    /**
     * @return string
     */
    public function getRequestContentType(): string;

    /**
     * @return array
     */
    public function getRequestHeaders(): array;

    /**
     * @return string
     */
    public function getRequestPath(): string;

    /**
     * @return array
     */
    public function getRequestQueryParameters(): array;

    /**
     * @return string|null
     */
    public function getResponseBodyType(): ?string;

    /**
     * @return bool
     */
    public function hasRequestBody(): bool;
}
