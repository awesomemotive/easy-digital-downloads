<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogImage;

/**
 * Builder for model CatalogImage
 *
 * @see CatalogImage
 */
class CatalogImageBuilder
{
    /**
     * @var CatalogImage
     */
    private $instance;

    private function __construct(CatalogImage $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Image Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogImage());
    }

    /**
     * Sets name field.
     *
     * @param string|null $value
     */
    public function name(?string $value): self
    {
        $this->instance->setName($value);
        return $this;
    }

    /**
     * Unsets name field.
     */
    public function unsetName(): self
    {
        $this->instance->unsetName();
        return $this;
    }

    /**
     * Sets url field.
     *
     * @param string|null $value
     */
    public function url(?string $value): self
    {
        $this->instance->setUrl($value);
        return $this;
    }

    /**
     * Unsets url field.
     */
    public function unsetUrl(): self
    {
        $this->instance->unsetUrl();
        return $this;
    }

    /**
     * Sets caption field.
     *
     * @param string|null $value
     */
    public function caption(?string $value): self
    {
        $this->instance->setCaption($value);
        return $this;
    }

    /**
     * Unsets caption field.
     */
    public function unsetCaption(): self
    {
        $this->instance->unsetCaption();
        return $this;
    }

    /**
     * Sets photo studio order id field.
     *
     * @param string|null $value
     */
    public function photoStudioOrderId(?string $value): self
    {
        $this->instance->setPhotoStudioOrderId($value);
        return $this;
    }

    /**
     * Unsets photo studio order id field.
     */
    public function unsetPhotoStudioOrderId(): self
    {
        $this->instance->unsetPhotoStudioOrderId();
        return $this;
    }

    /**
     * Initializes a new Catalog Image object.
     */
    public function build(): CatalogImage
    {
        return CoreHelper::clone($this->instance);
    }
}
