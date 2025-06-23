<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogObject;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListCatalogResponse;

/**
 * Builder for model ListCatalogResponse
 *
 * @see ListCatalogResponse
 */
class ListCatalogResponseBuilder
{
    /**
     * @var ListCatalogResponse
     */
    private $instance;

    private function __construct(ListCatalogResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Catalog Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListCatalogResponse());
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
        return $this;
    }

    /**
     * Sets objects field.
     *
     * @param CatalogObject[]|null $value
     */
    public function objects(?array $value): self
    {
        $this->instance->setObjects($value);
        return $this;
    }

    /**
     * Initializes a new List Catalog Response object.
     */
    public function build(): ListCatalogResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
