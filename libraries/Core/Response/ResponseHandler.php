<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\Response;

use EDD\Vendor\Core\Response\Types\DeserializableType;
use EDD\Vendor\Core\Response\Types\ErrorType;
use EDD\Vendor\Core\Response\Types\ResponseMultiType;
use EDD\Vendor\Core\Response\Types\ResponseType;
use EDD\Vendor\Core\Utils\XmlDeserializer;
use EDD\Vendor\CoreInterfaces\Core\Format;

class ResponseHandler
{
    private $format = Format::SCALAR;
    private $deserializableType;
    private $responseType;
    private $responseMultiType;
    private $responseError;
    private $useApiResponse = false;
    private $nullableType = false;

    public function __construct()
    {
        $this->responseError = new ResponseError();
        $this->deserializableType = new DeserializableType();
        $this->responseType = new ResponseType();
        $this->responseMultiType = new ResponseMultiType();
    }

    /**
     * Associates an ErrorType object to the statusCode provided.
     */
    public function throwErrorOn(string $statusCode, ErrorType $error): self
    {
        $this->responseError->addError($statusCode, $error);
        return $this;
    }

    /**
     * Wrap the actual success/failure response in ApiResponse
     */
    public function returnApiResponse(): self
    {
        $this->useApiResponse = true;
        $this->responseError->returnApiResponse();
        return $this;
    }

    /**
     * Sets the nullOn404 flag in ResponseError.
     */
    public function nullOn404(): self
    {
        $this->responseError->nullOn404();
        return $this;
    }

    /**
     * Sets the return type as nullable.
     */
    public function nullableType(): self
    {
        $this->nullableType = true;
        return $this;
    }

    /**
     * Sets the deserializer method to the one provided, for deserializableType.
     */
    public function deserializerMethod(callable $deserializerMethod): self
    {
        $this->deserializableType->setDeserializerMethod($deserializerMethod);
        return $this;
    }

    /**
     * Sets response type to the one provided and format to JSON.
     *
     * @param string $responseClass Response type class
     * @param int $dimensions Dimensions to be provided in case of an array
     */
    public function type(string $responseClass, int $dimensions = 0): self
    {
        $this->format = Format::JSON;
        $this->responseType->setResponseClass($responseClass);
        $this->responseType->setDimensions($dimensions);
        return $this;
    }

    /**
     * Sets response type to the one provided and format to XML.
     *
     * @param string $responseClass Type of Response class
     * @param string $rootName Name of the root in xml response
     */
    public function typeXml(string $responseClass, string $rootName): self
    {
        $this->format = Format::XML;
        $this->responseType->setResponseClass($responseClass);
        $this->responseType->setXmlDeserializer(function ($value, $class) use ($rootName) {
            return (new XmlDeserializer())->deserialize($value, $rootName, $class);
        });
        return $this;
    }

    /**
     * Sets response type to the one provided and format to XML.
     *
     * @param string $responseClass Type of Response class
     * @param string $rootName Name of the root for map in xml response
     */
    public function typeXmlMap(string $responseClass, string $rootName): self
    {
        $this->format = Format::XML;
        $this->responseType->setResponseClass($responseClass);
        $this->responseType->setXmlDeserializer(function ($value, $class) use ($rootName): ?array {
            return (new XmlDeserializer())->deserializeToMap($value, $rootName, $class);
        });
        return $this;
    }

    /**
     * Sets response type to the one provided and format to XML.
     *
     * @param string $responseClass Type of Response class
     * @param string $rootName Name of the root for array in xml response
     * @param string $itemName Name of each item in array
     */
    public function typeXmlArray(string $responseClass, string $rootName, string $itemName): self
    {
        $this->format = Format::XML;
        $this->responseType->setResponseClass($responseClass);
        $this->responseType->setXmlDeserializer(function ($value, $class) use ($rootName, $itemName): ?array {
            return (new XmlDeserializer())->deserializeToArray($value, $rootName, $itemName, $class);
        });
        return $this;
    }

    /**
     * @param string $typeGroup                Group of types in string format i.e. oneOf(...), anyOf(...)
     * @param string[] $typeGroupDeserializers Methods required for deserialization of specific types in
     *                                         in the provided typeGroup, should be an array in the format:
     *                                         ['path/to/method returnType', ...]. Default: []
     */
    public function typeGroup(string $typeGroup, array $typeGroupDeserializers = []): self
    {
        $this->format = Format::JSON;
        $this->responseMultiType->setTypeGroup($typeGroup);
        $this->responseMultiType->setDeserializers($typeGroupDeserializers);
        return $this;
    }

    /**
     * Returns current set format.
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Returns response from the context provided.
     *
     * @param Context $context
     * @return mixed
     */
    public function getResult(Context $context)
    {
        if ($context->isFailure()) {
            return $this->responseError->getResult($context);
        }
        if ($this->nullableType && $context->isBodyMissing()) {
            return $this->getResponse($context, null);
        }
        $result = $this->deserializableType->getFrom($context);
        $result = $result ?? $this->responseType->getFrom($context);
        $result = $result ?? $this->responseMultiType->getFrom($context);
        $result = $result ?? $context->getResponseBody();

        return $this->getResponse($context, $result);
    }

    private function getResponse(Context $context, $result)
    {
        if ($this->useApiResponse) {
            return $context->toApiResponse($result);
        }
        return $result;
    }
}
