<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\Response;

use EDD\Vendor\Core\Response\Types\ErrorType;

class ResponseError
{
    /**
     * @var array<string,ErrorType>
     */
    private $errors;
    private $useApiResponse = false;
    private $mapErrorTypes = false;
    private $nullOn404 = false;

    /**
     * Adds an error to the errors array with the errorCode and ErrorType provided.
     */
    public function addError(string $errorCode, ErrorType $error): void
    {
        $this->errors[$errorCode] = $error;
    }

    /**
     * Sets the useApiResponse flag.
     */
    public function returnApiResponse(): void
    {
        $this->useApiResponse = true;
    }

    /**
     * Sets the mapErrorTypes flag.
     */
    public function mapErrorTypesInApiResponse()
    {
        $this->mapErrorTypes = true;
    }

    /**
     * Sets the nullOn404 flag.
     */
    public function nullOn404(): void
    {
        $this->nullOn404 = true;
    }

    /**
     * Returns calculated result on failure or throws an exception.
     */
    public function getResult(Context $context)
    {
        if ($this->useApiResponse) {
            return $this->getApiResponse($context);
        }
        $statusCode = $context->getResponse()->getStatusCode();
        if ($this->shouldReturnNull($statusCode)) {
            return null;
        }

        $errorType = $this->getErrorType($statusCode);
        if (empty($errorType)) {
            throw $context->toApiException('HTTP Response Not OK');
        }

        throw $errorType->throwable($context);
    }

    private function getApiResponse(Context $context)
    {
        $statusCode = $context->getResponse()->getStatusCode();
        if ($this->shouldReturnNull($statusCode)) {
            return $context->toApiResponse(null);
        }

        $errorType = $this->getErrorType($statusCode);
        if (!$this->mapErrorTypes || empty($errorType)) {
            return $context->toApiResponse($context->getResponseBody());
        }

        return $context->toApiResponseWithMappedType($errorType->getClassName());
    }

    private function getErrorType(int $statusCode): ?ErrorType
    {
        if (isset($this->errors[strval($statusCode)])) {
            return $this->errors[strval($statusCode)];
        }
        if (isset($this->errors[strval(0)])) {
            return $this->errors[strval(0)];
        }
        return null;
    }

    private function shouldReturnNull(int $statusCode): bool
    {
        if (!$this->nullOn404) {
            return false;
        }
        return $statusCode === 404;
    }
}
