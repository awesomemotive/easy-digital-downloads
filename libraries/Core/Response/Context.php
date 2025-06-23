<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\Response;

use EDD\Vendor\Core\Client;
use EDD\Vendor\Core\Utils\JsonHelper;
use EDD\Vendor\CoreInterfaces\Core\ContextInterface;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestInterface;
use EDD\Vendor\CoreInterfaces\Core\Response\ResponseInterface;

class Context implements ContextInterface
{
    private $request;
    private $response;
    private $converter;
    private $jsonHelper;

    /**
     * Initializes a new Context with the request, response, jsonHelper and the converter set.
     */
    public function __construct(RequestInterface $request, ResponseInterface $response, Client $client)
    {
        $this->request = $request;
        $this->response = $response;
        $this->converter = Client::getConverter($client);
        $this->jsonHelper = Client::getJsonHelper($client);
    }

    /**
     * Returns Request object.
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Returns Response object.
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Returns Response body as a scalar or an associative array.
     */
    public function getResponseBody()
    {
        $responseBody = $this->response->getBody();
        if (is_object($responseBody)) {
            return (array) $responseBody;
        }
        return $responseBody;
    }

    /**
     * Is successful response.
     */
    public function isFailure(): bool
    {
        $statusCode = $this->response->getStatusCode();
        return $statusCode !== min(max($statusCode, 200), 208); // [200,208] = HTTP OK
    }

    /**
     * Is response body missing.
     */
    public function isBodyMissing(): bool
    {
        $rawBody = $this->response->getRawBody();
        return trim($rawBody) === '';
    }

    /**
     * Returns JsonHelper object.
     */
    public function getJsonHelper(): JsonHelper
    {
        return $this->jsonHelper;
    }

    /**
     * Returns an ApiException with errorMessage and childClass set, if not null.
     */
    public function toApiException(string $errorMessage, ?string $childClass = null)
    {
        $responseBody = $this->response->getBody();
        if (is_null($childClass)) {
            return $this->converter->createApiException($errorMessage, $this->request, $this->response);
        }
        if (!is_object($responseBody)) {
            return $this->converter->createApiException($errorMessage, $this->request, $this->response);
        }
        $responseBody->reason = $errorMessage;
        $responseBody->request = $this->request->convert();
        $responseBody->response = $this->response->convert($this->converter);
        return $this->jsonHelper->mapClass($responseBody, $childClass);
    }

    /**
     * Returns a MockApiResponse object from the context and the deserializedBody provided.
     */
    public function toApiResponse($deserializedBody)
    {
        return $this->converter->createApiResponse($this, $deserializedBody);
    }
}
