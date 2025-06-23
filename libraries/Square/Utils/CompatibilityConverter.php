<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Utils;

use EDD\Vendor\CoreInterfaces\Core\ContextInterface;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestInterface;
use EDD\Vendor\CoreInterfaces\Core\Response\ResponseInterface;
use EDD\Vendor\CoreInterfaces\Sdk\ConverterInterface;
use EDD\Vendor\Square\Exceptions\ApiException;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Http\HttpContext;
use EDD\Vendor\Square\Http\HttpRequest;
use EDD\Vendor\Square\Http\HttpResponse;

class CompatibilityConverter implements ConverterInterface
{
    public function createApiException(
        string $message,
        RequestInterface $request,
        ?ResponseInterface $response
    ): ApiException {
        $response = $response == null ? null : $this->createHttpResponse($response);
        return new ApiException($message, $this->createHttpRequest($request), $response);
    }

    public function createHttpContext(ContextInterface $context): HttpContext
    {
        return new HttpContext(
            $this->createHttpRequest($context->getRequest()),
            $this->createHttpResponse($context->getResponse())
        );
    }

    public function createHttpRequest(RequestInterface $request): HttpRequest
    {
        return new HttpRequest(
            $request->getHttpMethod(),
            $request->getHeaders(),
            $request->getQueryUrl(),
            $request->getParameters()
        );
    }

    public function createHttpResponse(ResponseInterface $response): HttpResponse
    {
        return new HttpResponse($response->getStatusCode(), $response->getHeaders(), $response->getRawBody());
    }

    public function createApiResponse(ContextInterface $context, $deserializedBody): ApiResponse
    {
        return ApiResponse::createFromContext(
            $context->getResponse()->getBody(),
            $deserializedBody,
            $this->createHttpContext($context)
        );
    }

    public function createFileWrapper(string $realFilePath, ?string $mimeType, ?string $filename): FileWrapper
    {
        return FileWrapper::createFromPath($realFilePath, $mimeType, $filename);
    }
}
