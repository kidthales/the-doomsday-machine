<?php

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
