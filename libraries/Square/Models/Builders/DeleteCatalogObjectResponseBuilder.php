<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteCatalogObjectResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteCatalogObjectResponse
 *
 * @see DeleteCatalogObjectResponse
 */
class DeleteCatalogObjectResponseBuilder
{
    /**
     * @var DeleteCatalogObjectResponse
     */
    private $instance;

    private function __construct(DeleteCatalogObjectResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Catalog Object Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteCatalogObjectResponse());
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
     * Sets deleted object ids field.
     *
     * @param string[]|null $value
     */
    public function deletedObjectIds(?array $value): self
    {
        $this->instance->setDeletedObjectIds($value);
        return $this;
    }

    /**
     * Sets deleted at field.
     *
     * @param string|null $value
     */
    public function deletedAt(?string $value): self
    {
        $this->instance->setDeletedAt($value);
        return $this;
    }

    /**
     * Initializes a new Delete Catalog Object Response object.
     */
    public function build(): DeleteCatalogObjectResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
