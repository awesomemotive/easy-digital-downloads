<?php

namespace EDD\Vendor\Core\Response\Types;

use Closure;
use EDD\Vendor\Core\Response\Context;

class DeserializableType
{
    /**
     * @var callable|null
     */
    private $deserializerMethod;

    /**
     * Sets deserializer method to the one provided.
     */
    public function setDeserializerMethod(callable $deserializerMethod): void
    {
        $this->deserializerMethod = $deserializerMethod;
    }

    /**
     * Returns the deserializer method if already set.
     */
    public function getFrom(Context $context)
    {
        if (is_null($this->deserializerMethod)) {
            return null;
        }
        return Closure::fromCallable($this->deserializerMethod)($context->getResponse()->getBody());
    }
}
