<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\Logger\Configuration;

use EDD\Vendor\Core\Logger\LoggerConstants;

class BaseHttpLoggingConfiguration
{
    private $logBody;
    private $logHeaders;
    private $headersToInclude;
    private $headersToExclude;
    private $headersToUnmask;

    /**
     * Construct an instance of ResponseConfig for logging
     *
     * @param bool $logBody
     * @param bool $logHeaders
     * @param string[] $headersToInclude
     * @param string[] $headersToExclude
     * @param string[] $headersToUnmask
     */
    public function __construct(
        bool $logBody,
        bool $logHeaders,
        array $headersToInclude,
        array $headersToExclude,
        array $headersToUnmask
    ) {
        $this->logBody = $logBody;
        $this->logHeaders = $logHeaders;
        $this->headersToInclude = array_map('strtolower', $headersToInclude);
        $this->headersToExclude = empty($headersToInclude) ? array_map('strtolower', $headersToExclude) : [];
        $this->headersToUnmask = array_merge(
            array_map('strtolower', LoggerConstants::NON_SENSITIVE_HEADERS),
            array_map('strtolower', $headersToUnmask)
        );
    }

    /**
     * Indicates whether to log the body.
     */
    public function shouldLogBody(): bool
    {
        return $this->logBody;
    }

    /**
     * Indicates whether to log the headers.
     */
    public function shouldLogHeaders(): bool
    {
        return $this->logHeaders;
    }

    /**
     * Select the headers from the list of provided headers for logging.
     *
     * @param string[] $headers
     * @param bool $maskSensitiveHeaders
     *
     * @return string[]
     */
    public function getLoggableHeaders(array $headers, bool $maskSensitiveHeaders): array
    {
        $sensitiveHeaders = [];
        $filteredHeaders = array_filter($headers, function ($key) use ($maskSensitiveHeaders, &$sensitiveHeaders) {
            $lowerCaseKey = strtolower(strval($key));
            if ($maskSensitiveHeaders && $this->isSensitiveHeader($lowerCaseKey)) {
                $sensitiveHeaders[$key] = '**Redacted**';
            }
            if (
                (empty($this->headersToInclude) || in_array($lowerCaseKey, $this->headersToInclude, true)) &&
                (empty($this->headersToExclude) || !in_array($lowerCaseKey, $this->headersToExclude, true))
            ) {
                return true;
            }
            unset($sensitiveHeaders[$key]);
            return false;
        }, ARRAY_FILTER_USE_KEY);

        return array_merge($filteredHeaders, $sensitiveHeaders);
    }

    private function isSensitiveHeader($headerKey): bool
    {
        if (in_array($headerKey, $this->headersToUnmask, true)) {
            return false;
        }
        return true;
    }
}
