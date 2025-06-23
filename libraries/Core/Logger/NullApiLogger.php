<?php

namespace EDD\Vendor\Core\Logger;

use EDD\Vendor\CoreInterfaces\Core\Logger\ApiLoggerInterface;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestInterface;
use EDD\Vendor\CoreInterfaces\Core\Response\ResponseInterface;

class NullApiLogger implements ApiLoggerInterface
{
    /**
     * @inheritDoc
     */
    public function logRequest(RequestInterface $request): void
    {
        // noop
    }

    /**
     * @inheritDoc
     */
    public function logResponse(ResponseInterface $response): void
    {
        // noop
    }
}
