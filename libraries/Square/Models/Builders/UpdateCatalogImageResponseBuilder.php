<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogObject;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\UpdateCatalogImageResponse;

/**
 * Builder for model UpdateCatalogImageResponse
 *
 * @see UpdateCatalogImageResponse
 */
class UpdateCatalogImageResponseBuilder
{
    /**
     * @var UpdateCatalogImageResponse
     */
    private $instance;

    private function __construct(UpdateCatalogImageResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Catalog Image Response Builder object.
     */
    public static function init(): self
    {
        return new self(new UpdateCatalogImageResponse());
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
     * Sets image field.
     *
     * @param CatalogObject|null $value
     */
    public function image(?CatalogObject $value): self
    {
        $this->instance->setImage($value);
        return $this;
    }

    /**
     * Initializes a new Update Catalog Image Response object.
     */
    public function build(): UpdateCatalogImageResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
