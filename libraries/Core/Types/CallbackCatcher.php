<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\Types;

use EDD\Vendor\CoreInterfaces\Core\ContextInterface;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestInterface;
use EDD\Vendor\CoreInterfaces\Core\Response\ResponseInterface;
use EDD\Vendor\CoreInterfaces\Sdk\ConverterInterface;
use EDD\Vendor\Core\Types\Sdk\CoreCallback;

class CallbackCatcher extends CoreCallback
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * Create instance
     */
    public function __construct()
    {
        $instance = $this;
        parent::__construct(null, function (ContextInterface $httpContext) use ($instance): void {
            $instance->request = $httpContext->getRequest();
            $instance->response = $httpContext->getResponse();
        });
    }

    /**
     * Get the Request object associated with this API call
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Get the Response object associated with this API call
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function callOnBeforeWithConversion(RequestInterface $request, ConverterInterface $converter)
    {
        parent::callOnBeforeRequest($request);
    }

    public function callOnAfterWithConversion(ContextInterface $context, ConverterInterface $converter)
    {
        parent::callOnAfterRequest($context);
    }
}
