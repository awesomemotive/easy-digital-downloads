<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogCategory;
use EDD\Vendor\Square\Models\CatalogEcomSeoData;
use EDD\Vendor\Square\Models\CatalogObjectCategory;
use EDD\Vendor\Square\Models\CategoryPathToRootNode;

/**
 * Builder for model CatalogCategory
 *
 * @see CatalogCategory
 */
class CatalogCategoryBuilder
{
    /**
     * @var CatalogCategory
     */
    private $instance;

    private function __construct(CatalogCategory $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Category Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogCategory());
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
     * Sets image ids field.
     *
     * @param string[]|null $value
     */
    public function imageIds(?array $value): self
    {
        $this->instance->setImageIds($value);
        return $this;
    }

    /**
     * Unsets image ids field.
     */
    public function unsetImageIds(): self
    {
        $this->instance->unsetImageIds();
        return $this;
    }

    /**
     * Sets category type field.
     *
     * @param string|null $value
     */
    public function categoryType(?string $value): self
    {
        $this->instance->setCategoryType($value);
        return $this;
    }

    /**
     * Sets parent category field.
     *
     * @param CatalogObjectCategory|null $value
     */
    public function parentCategory(?CatalogObjectCategory $value): self
    {
        $this->instance->setParentCategory($value);
        return $this;
    }

    /**
     * Sets is top level field.
     *
     * @param bool|null $value
     */
    public function isTopLevel(?bool $value): self
    {
        $this->instance->setIsTopLevel($value);
        return $this;
    }

    /**
     * Unsets is top level field.
     */
    public function unsetIsTopLevel(): self
    {
        $this->instance->unsetIsTopLevel();
        return $this;
    }

    /**
     * Sets channels field.
     *
     * @param string[]|null $value
     */
    public function channels(?array $value): self
    {
        $this->instance->setChannels($value);
        return $this;
    }

    /**
     * Unsets channels field.
     */
    public function unsetChannels(): self
    {
        $this->instance->unsetChannels();
        return $this;
    }

    /**
     * Sets availability period ids field.
     *
     * @param string[]|null $value
     */
    public function availabilityPeriodIds(?array $value): self
    {
        $this->instance->setAvailabilityPeriodIds($value);
        return $this;
    }

    /**
     * Unsets availability period ids field.
     */
    public function unsetAvailabilityPeriodIds(): self
    {
        $this->instance->unsetAvailabilityPeriodIds();
        return $this;
    }

    /**
     * Sets online visibility field.
     *
     * @param bool|null $value
     */
    public function onlineVisibility(?bool $value): self
    {
        $this->instance->setOnlineVisibility($value);
        return $this;
    }

    /**
     * Unsets online visibility field.
     */
    public function unsetOnlineVisibility(): self
    {
        $this->instance->unsetOnlineVisibility();
        return $this;
    }

    /**
     * Sets root category field.
     *
     * @param string|null $value
     */
    public function rootCategory(?string $value): self
    {
        $this->instance->setRootCategory($value);
        return $this;
    }

    /**
     * Sets ecom seo data field.
     *
     * @param CatalogEcomSeoData|null $value
     */
    public function ecomSeoData(?CatalogEcomSeoData $value): self
    {
        $this->instance->setEcomSeoData($value);
        return $this;
    }

    /**
     * Sets path to root field.
     *
     * @param CategoryPathToRootNode[]|null $value
     */
    public function pathToRoot(?array $value): self
    {
        $this->instance->setPathToRoot($value);
        return $this;
    }

    /**
     * Unsets path to root field.
     */
    public function unsetPathToRoot(): self
    {
        $this->instance->unsetPathToRoot();
        return $this;
    }

    /**
     * Initializes a new Catalog Category object.
     */
    public function build(): CatalogCategory
    {
        return CoreHelper::clone($this->instance);
    }
}
