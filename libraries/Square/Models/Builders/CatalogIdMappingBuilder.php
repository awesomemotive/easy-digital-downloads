<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogIdMapping;

/**
 * Builder for model CatalogIdMapping
 *
 * @see CatalogIdMapping
 */
class CatalogIdMappingBuilder
{
    /**
     * @var CatalogIdMapping
     */
    private $instance;

    private function __construct(CatalogIdMapping $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Id Mapping Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogIdMapping());
    }

    /**
     * Sets client object id field.
     *
     * @param string|null $value
     */
    public function clientObjectId(?string $value): self
    {
        $this->instance->setClientObjectId($value);
        return $this;
    }

    /**
     * Unsets client object id field.
     */
    public function unsetClientObjectId(): self
    {
        $this->instance->unsetClientObjectId();
        return $this;
    }

    /**
     * Sets object id field.
     *
     * @param string|null $value
     */
    public function objectId(?string $value): self
    {
        $this->instance->setObjectId($value);
        return $this;
    }

    /**
     * Unsets object id field.
     */
    public function unsetObjectId(): self
    {
        $this->instance->unsetObjectId();
        return $this;
    }

    /**
     * Initializes a new Catalog Id Mapping object.
     */
    public function build(): CatalogIdMapping
    {
        return CoreHelper::clone($this->instance);
    }
}
