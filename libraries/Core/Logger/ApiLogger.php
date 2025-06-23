<?php

namespace EDD\Vendor\Core\Logger;

use EDD\Vendor\Core\Logger\Configuration\LoggingConfiguration;
use EDD\Vendor\CoreInterfaces\Core\Logger\ApiLoggerInterface;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestInterface;
use EDD\Vendor\CoreInterfaces\Core\Response\ResponseInterface;

class ApiLogger implements ApiLoggerInterface
{
    private $config;

    public function __construct(LoggingConfiguration $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function logRequest(RequestInterface $request): void
    {
        $contentType = $this->getHeaderValue(LoggerConstants::CONTENT_TYPE_HEADER, $request->getHeaders());

        $this->config->logMessage(
            'Request {' . LoggerConstants::METHOD . '} {' . LoggerConstants::URL .
            '} {' . LoggerConstants::CONTENT_TYPE . '}',
            [
                LoggerConstants::METHOD => $request->getHttpMethod(),
                LoggerConstants::URL => $this->getRequestUrl($request),
                LoggerConstants::CONTENT_TYPE => $contentType
            ]
        );

        if ($this->config->getRequestConfig()->shouldLogHeaders()) {
            $headers = $this->config->getRequestConfig()->getLoggableHeaders(
                $request->getHeaders(),
                $this->config->shouldMaskSensitiveHeaders()
            );
            $this->config->logMessage(
                'Request Headers {' . LoggerConstants::HEADERS . '}',
                [LoggerConstants::HEADERS => $headers]
            );
        }

        if ($this->config->getRequestConfig()->shouldLogBody()) {
            $body = $request->getParameters();
            if (empty($body)) {
                $body = $request->getBody();
            }
            $this->config->logMessage(
                'Request Body {' . LoggerConstants::BODY . '}',
                [LoggerConstants::BODY => $body]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function logResponse(ResponseInterface $response): void
    {
        $contentLength = $this->getHeaderValue(LoggerConstants::CONTENT_LENGTH_HEADER, $response->getHeaders());
        $contentType = $this->getHeaderValue(LoggerConstants::CONTENT_TYPE_HEADER, $response->getHeaders());

        $this->config->logMessage(
            'Response {' . LoggerConstants::STATUS_CODE . '} {' . LoggerConstants::CONTENT_LENGTH .
            '} {' . LoggerConstants::CONTENT_TYPE . '}',
            [
                LoggerConstants::STATUS_CODE => $response->getStatusCode(),
                LoggerConstants::CONTENT_LENGTH => $contentLength,
                LoggerConstants::CONTENT_TYPE => $contentType
            ]
        );

        if ($this->config->getResponseConfig()->shouldLogHeaders()) {
            $headers = $this->config->getResponseConfig()->getLoggableHeaders(
                $response->getHeaders(),
                $this->config->shouldMaskSensitiveHeaders()
            );
            $this->config->logMessage(
                'Response Headers {' . LoggerConstants::HEADERS . '}',
                [LoggerConstants::HEADERS => $headers]
            );
        }

        if ($this->config->getResponseConfig()->shouldLogBody()) {
            $this->config->logMessage(
                'Response Body {' . LoggerConstants::BODY . '}',
                [LoggerConstants::BODY => $response->getRawBody()]
            );
        }
    }

    private function getHeaderValue(string $key, array $headers): ?string
    {
        $key = strtolower($key);
        foreach ($headers as $k => $value) {
            if (strtolower($k) === $key) {
                return $value;
            }
        }
        return null;
    }

    private function getRequestUrl(RequestInterface $request): string
    {
        $queryUrl = $request->getQueryUrl();
        if ($this->config->getRequestConfig()->shouldIncludeQueryInPath()) {
            return $queryUrl;
        }
        return explode("?", $queryUrl)[0];
    }
}
