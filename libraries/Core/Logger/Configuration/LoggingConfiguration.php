<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\Logger\Configuration;

use EDD\Vendor\Core\Logger\ConsoleLogger;
use EDD\Vendor\Psr\Log\LoggerInterface;

class LoggingConfiguration
{
    private $logger;
    private $level;
    private $maskSensitiveHeaders;
    private $requestConfig;
    private $responseConfig;

    public function __construct(
        ?LoggerInterface $logger,
        string $level,
        bool $maskSensitiveHeaders,
        RequestConfiguration $requestConfig,
        ResponseConfiguration $responseConfig
    ) {
        $this->logger = $logger ?? new ConsoleLogger('printf');
        $this->level = $level;
        $this->maskSensitiveHeaders = $maskSensitiveHeaders;
        $this->requestConfig = $requestConfig;
        $this->responseConfig = $responseConfig;
    }

    /**
     * Log the given message using the context array. This function uses the
     * LogLevel and Logger instance set via constructor of this class.
     */
    public function logMessage(string $message, array $context): void
    {
        $this->logger->log($this->level, $message, $context);
    }

    /**
     * Indicates whether sensitive headers should be masked in logs.
     *
     * @return bool True if sensitive headers should be masked, false otherwise.
     */
    public function shouldMaskSensitiveHeaders(): bool
    {
        return $this->maskSensitiveHeaders;
    }

    /**
     * Gets the request configuration for logging.
     *
     * @return RequestConfiguration The request configuration.
     */
    public function getRequestConfig(): RequestConfiguration
    {
        return $this->requestConfig;
    }

    /**
     * Gets the response configuration for logging.
     *
     * @return ResponseConfiguration The response configuration.
     */
    public function getResponseConfig(): ResponseConfiguration
    {
        return $this->responseConfig;
    }
}
