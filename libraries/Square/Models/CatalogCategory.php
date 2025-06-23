<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * A category to which a `CatalogItem` instance belongs.
 */
class CatalogCategory implements \JsonSerializable
{
    /**
     * @var array
     */
    private $name = [];

    /**
     * @var array
     */
    private $imageIds = [];

    /**
     * @var string|null
     */
    private $categoryType;

    /**
     * @var CatalogObjectCategory|null
     */
    private $parentCategory;

    /**
     * @var array
     */
    private $isTopLevel = [];

    /**
     * @var array
     */
    private $channels = [];

    /**
     * @var array
     */
    private $availabilityPeriodIds = [];

    /**
     * @var array
     */
    private $onlineVisibility = [];

    /**
     * @var string|null
     */
    private $rootCategory;

    /**
     * @var CatalogEcomSeoData|null
     */
    private $ecomSeoData;

    /**
     * @var array
     */
    private $pathToRoot = [];

    /**
     * Returns Name.
     * The category name. This is a searchable attribute for use in applicable query filters, and its value
     * length is of Unicode code points.
     */
    public function getName(): ?string
    {
        if (count($this->name) == 0) {
            return null;
        }
        return $this->name['value'];
    }

    /**
     * Sets Name.
     * The category name. This is a searchable attribute for use in applicable query filters, and its value
     * length is of Unicode code points.
     *
     * @maps name
     */
    public function setName(?string $name): void
    {
        $this->name['value'] = $name;
    }

    /**
     * Unsets Name.
     * The category name. This is a searchable attribute for use in applicable query filters, and its value
     * length is of Unicode code points.
     */
    public function unsetName(): void
    {
        $this->name = [];
    }

    /**
     * Returns Image Ids.
     * The IDs of images associated with this `CatalogCategory` instance.
     * Currently these images are not displayed by Square, but are free to be displayed in 3rd party
     * applications.
     *
     * @return string[]|null
     */
    public function getImageIds(): ?array
    {
        if (count($this->imageIds) == 0) {
            return null;
        }
        return $this->imageIds['value'];
    }

    /**
     * Sets Image Ids.
     * The IDs of images associated with this `CatalogCategory` instance.
     * Currently these images are not displayed by Square, but are free to be displayed in 3rd party
     * applications.
     *
     * @maps image_ids
     *
     * @param string[]|null $imageIds
     */
    public function setImageIds(?array $imageIds): void
    {
        $this->imageIds['value'] = $imageIds;
    }

    /**
     * Unsets Image Ids.
     * The IDs of images associated with this `CatalogCategory` instance.
     * Currently these images are not displayed by Square, but are free to be displayed in 3rd party
     * applications.
     */
    public function unsetImageIds(): void
    {
        $this->imageIds = [];
    }

    /**
     * Returns Category Type.
     * Indicates the type of a category.
     */
    public function getCategoryType(): ?string
    {
        return $this->categoryType;
    }

    /**
     * Sets Category Type.
     * Indicates the type of a category.
     *
     * @maps category_type
     */
    public function setCategoryType(?string $categoryType): void
    {
        $this->categoryType = $categoryType;
    }

    /**
     * Returns Parent Category.
     * A category that can be assigned to an item or a parent category that can be assigned
     * to another category. For example, a clothing category can be assigned to a t-shirt item or
     * be made as the parent category to the pants category.
     */
    public function getParentCategory(): ?CatalogObjectCategory
    {
        return $this->parentCategory;
    }

    /**
     * Sets Parent Category.
     * A category that can be assigned to an item or a parent category that can be assigned
     * to another category. For example, a clothing category can be assigned to a t-shirt item or
     * be made as the parent category to the pants category.
     *
     * @maps parent_category
     */
    public function setParentCategory(?CatalogObjectCategory $parentCategory): void
    {
        $this->parentCategory = $parentCategory;
    }

    /**
     * Returns Is Top Level.
     * Indicates whether a category is a top level category, which does not have any parent_category.
     */
    public function getIsTopLevel(): ?bool
    {
        if (count($this->isTopLevel) == 0) {
            return null;
        }
        return $this->isTopLevel['value'];
    }

    /**
     * Sets Is Top Level.
     * Indicates whether a category is a top level category, which does not have any parent_category.
     *
     * @maps is_top_level
     */
    public function setIsTopLevel(?bool $isTopLevel): void
    {
        $this->isTopLevel['value'] = $isTopLevel;
    }

    /**
     * Unsets Is Top Level.
     * Indicates whether a category is a top level category, which does not have any parent_category.
     */
    public function unsetIsTopLevel(): void
    {
        $this->isTopLevel = [];
    }

    /**
     * Returns Channels.
     * A list of IDs representing channels, such as a EDD\Vendor\Square Online site, where the category can be made
     * visible.
     *
     * @return string[]|null
     */
    public function getChannels(): ?array
    {
        if (count($this->channels) == 0) {
            return null;
        }
        return $this->channels['value'];
    }

    /**
     * Sets Channels.
     * A list of IDs representing channels, such as a EDD\Vendor\Square Online site, where the category can be made
     * visible.
     *
     * @maps channels
     *
     * @param string[]|null $channels
     */
    public function setChannels(?array $channels): void
    {
        $this->channels['value'] = $channels;
    }

    /**
     * Unsets Channels.
     * A list of IDs representing channels, such as a EDD\Vendor\Square Online site, where the category can be made
     * visible.
     */
    public function unsetChannels(): void
    {
        $this->channels = [];
    }

    /**
     * Returns Availability Period Ids.
     * The IDs of the `CatalogAvailabilityPeriod` objects associated with the category.
     *
     * @return string[]|null
     */
    public function getAvailabilityPeriodIds(): ?array
    {
        if (count($this->availabilityPeriodIds) == 0) {
            return null;
        }
        return $this->availabilityPeriodIds['value'];
    }

    /**
     * Sets Availability Period Ids.
     * The IDs of the `CatalogAvailabilityPeriod` objects associated with the category.
     *
     * @maps availability_period_ids
     *
     * @param string[]|null $availabilityPeriodIds
     */
    public function setAvailabilityPeriodIds(?array $availabilityPeriodIds): void
    {
        $this->availabilityPeriodIds['value'] = $availabilityPeriodIds;
    }

    /**
     * Unsets Availability Period Ids.
     * The IDs of the `CatalogAvailabilityPeriod` objects associated with the category.
     */
    public function unsetAvailabilityPeriodIds(): void
    {
        $this->availabilityPeriodIds = [];
    }

    /**
     * Returns Online Visibility.
     * Indicates whether the category is visible (`true`) or hidden (`false`) on all of the seller's EDD\Vendor\Square
     * Online sites.
     */
    public function getOnlineVisibility(): ?bool
    {
        if (count($this->onlineVisibility) == 0) {
            return null;
        }
        return $this->onlineVisibility['value'];
    }

    /**
     * Sets Online Visibility.
     * Indicates whether the category is visible (`true`) or hidden (`false`) on all of the seller's EDD\Vendor\Square
     * Online sites.
     *
     * @maps online_visibility
     */
    public function setOnlineVisibility(?bool $onlineVisibility): void
    {
        $this->onlineVisibility['value'] = $onlineVisibility;
    }

    /**
     * Unsets Online Visibility.
     * Indicates whether the category is visible (`true`) or hidden (`false`) on all of the seller's EDD\Vendor\Square
     * Online sites.
     */
    public function unsetOnlineVisibility(): void
    {
        $this->onlineVisibility = [];
    }

    /**
     * Returns Root Category.
     * The top-level category in a category hierarchy.
     */
    public function getRootCategory(): ?string
    {
        return $this->rootCategory;
    }

    /**
     * Sets Root Category.
     * The top-level category in a category hierarchy.
     *
     * @maps root_category
     */
    public function setRootCategory(?string $rootCategory): void
    {
        $this->rootCategory = $rootCategory;
    }

    /**
     * Returns Ecom Seo Data.
     * SEO data for for a seller's EDD\Vendor\Square Online store.
     */
    public function getEcomSeoData(): ?CatalogEcomSeoData
    {
        return $this->ecomSeoData;
    }

    /**
     * Sets Ecom Seo Data.
     * SEO data for for a seller's EDD\Vendor\Square Online store.
     *
     * @maps ecom_seo_data
     */
    public function setEcomSeoData(?CatalogEcomSeoData $ecomSeoData): void
    {
        $this->ecomSeoData = $ecomSeoData;
    }

    /**
     * Returns Path to Root.
     * The path from the category to its root category. The first node of the path is the parent of the
     * category
     * and the last is the root category. The path is empty if the category is a root category.
     *
     * @return CategoryPathToRootNode[]|null
     */
    public function getPathToRoot(): ?array
    {
        if (count($this->pathToRoot) == 0) {
            return null;
        }
        return $this->pathToRoot['value'];
    }

    /**
     * Sets Path to Root.
     * The path from the category to its root category. The first node of the path is the parent of the
     * category
     * and the last is the root category. The path is empty if the category is a root category.
     *
     * @maps path_to_root
     *
     * @param CategoryPathToRootNode[]|null $pathToRoot
     */
    public function setPathToRoot(?array $pathToRoot): void
    {
        $this->pathToRoot['value'] = $pathToRoot;
    }

    /**
     * Unsets Path to Root.
     * The path from the category to its root category. The first node of the path is the parent of the
     * category
     * and the last is the root category. The path is empty if the category is a root category.
     */
    public function unsetPathToRoot(): void
    {
        $this->pathToRoot = [];
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        if (!empty($this->name)) {
            $json['name']                    = $this->name['value'];
        }
        if (!empty($this->imageIds)) {
            $json['image_ids']               = $this->imageIds['value'];
        }
        if (isset($this->categoryType)) {
            $json['category_type']           = $this->categoryType;
        }
        if (isset($this->parentCategory)) {
            $json['parent_category']         = $this->parentCategory;
        }
        if (!empty($this->isTopLevel)) {
            $json['is_top_level']            = $this->isTopLevel['value'];
        }
        if (!empty($this->channels)) {
            $json['channels']                = $this->channels['value'];
        }
        if (!empty($this->availabilityPeriodIds)) {
            $json['availability_period_ids'] = $this->availabilityPeriodIds['value'];
        }
        if (!empty($this->onlineVisibility)) {
            $json['online_visibility']       = $this->onlineVisibility['value'];
        }
        if (isset($this->rootCategory)) {
            $json['root_category']           = $this->rootCategory;
        }
        if (isset($this->ecomSeoData)) {
            $json['ecom_seo_data']           = $this->ecomSeoData;
        }
        if (!empty($this->pathToRoot)) {
            $json['path_to_root']            = $this->pathToRoot['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
