<?php

declare(strict_types=1);

namespace App\HttpClient;

abstract class AbstractJsonApiEndpoint extends AbstractApiEndpoint
{
    /**
     * @inheritDoc
     */
    public function getRequestContentType(): string
    {
        return 'application/json';
    }
}
