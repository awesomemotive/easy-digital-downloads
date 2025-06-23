<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\RetrieveCatalogObjectRequest;

/**
 * Builder for model RetrieveCatalogObjectRequest
 *
 * @see RetrieveCatalogObjectRequest
 */
class RetrieveCatalogObjectRequestBuilder
{
    /**
     * @var RetrieveCatalogObjectRequest
     */
    private $instance;

    private function __construct(RetrieveCatalogObjectRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Catalog Object Request Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveCatalogObjectRequest());
    }

    /**
     * Sets include related objects field.
     *
     * @param bool|null $value
     */
    public function includeRelatedObjects(?bool $value): self
    {
        $this->instance->setIncludeRelatedObjects($value);
        return $this;
    }

    /**
     * Unsets include related objects field.
     */
    public function unsetIncludeRelatedObjects(): self
    {
        $this->instance->unsetIncludeRelatedObjects();
        return $this;
    }

    /**
     * Sets catalog version field.
     *
     * @param int|null $value
     */
    public function catalogVersion(?int $value): self
    {
        $this->instance->setCatalogVersion($value);
        return $this;
    }

    /**
     * Unsets catalog version field.
     */
    public function unsetCatalogVersion(): self
    {
        $this->instance->unsetCatalogVersion();
        return $this;
    }

    /**
     * Sets include category path to root field.
     *
     * @param bool|null $value
     */
    public function includeCategoryPathToRoot(?bool $value): self
    {
        $this->instance->setIncludeCategoryPathToRoot($value);
        return $this;
    }

    /**
     * Unsets include category path to root field.
     */
    public function unsetIncludeCategoryPathToRoot(): self
    {
        $this->instance->unsetIncludeCategoryPathToRoot();
        return $this;
    }

    /**
     * Initializes a new Retrieve Catalog Object Request object.
     */
    public function build(): RetrieveCatalogObjectRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
