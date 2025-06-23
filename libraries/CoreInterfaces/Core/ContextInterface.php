<?php

namespace EDD\Vendor\CoreInterfaces\Core;

use EDD\Vendor\CoreInterfaces\Core\Request\RequestInterface;
use EDD\Vendor\CoreInterfaces\Core\Response\ResponseInterface;

interface ContextInterface
{
    public function getRequest(): RequestInterface;
    public function getResponse(): ResponseInterface;
}
