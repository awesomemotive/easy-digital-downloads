<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\Response\Types;

use Closure;
use EDD\Vendor\Core\Response\Context;

class ResponseType
{
    /**
     * @var string|null
     */
    private $responseClass;

    /**
     * @var callable|null
     */
    private $xmlDeserializer;

    /**
     * @var int|null
     */
    private $dimensions;

    /**
     * Sets response class to the one provided.
     */
    public function setResponseClass(string $responseClass): void
    {
        $this->responseClass = $responseClass;
    }

    /**
     * Sets xml deserializer to the one provided.
     */
    public function setXmlDeserializer(callable $xmlDeserializer): void
    {
        $this->xmlDeserializer = $xmlDeserializer;
    }

    /**
     * Sets dimensions of the object.
     */
    public function setDimensions(int $dimensions): void
    {
        $this->dimensions = $dimensions;
    }

    /**
     * Returns ResponseClass from the context provided.
     */
    public function getFrom(Context $context)
    {
        if (is_null($this->responseClass) || $context->isBodyMissing()) {
            return null;
        }
        if (isset($this->xmlDeserializer)) {
            return Closure::fromCallable($this->xmlDeserializer)(
                $context->getResponse()->getRawBody(),
                $this->responseClass
            );
        }
        return $context->getJsonHelper()->mapClass(
            $context->getResponse()->getBody(),
            $this->responseClass,
            $this->dimensions
        );
    }
}
