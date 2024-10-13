<?php

namespace App\Entity\Discord\Api\Endpoint;

use App\Entity\Discord\Api\Dto\Application;
use App\HttpClient\AbstractJsonApiEndpoint;

/**
 * @see https://discord.com/developers/docs/resources/application#get-current-application
 */
class GetCurrentApplicationEndpoint extends AbstractJsonApiEndpoint
{
    /**
     * @return string
     */
    public static function getRequestMethod(): string
    {
        return 'GET';
    }

    /**
     * @return mixed
     */
    public function getRequestBody(): null
    {
        return null;
    }

    /**
     * @return string
     */
    public function getRequestPath(): string
    {
        return 'applications/@me';
    }

    /**
     * @return string
     */
    public function getResponseBodyType(): string
    {
        return Application::class;
    }

    /**
     * @return bool
     */
    public function hasRequestBody(): bool
    {
        return false;
    }
}
