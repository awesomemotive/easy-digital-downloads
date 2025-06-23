<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogEcomSeoData;
use EDD\Vendor\Square\Models\CatalogItem;
use EDD\Vendor\Square\Models\CatalogItemFoodAndBeverageDetails;
use EDD\Vendor\Square\Models\CatalogItemModifierListInfo;
use EDD\Vendor\Square\Models\CatalogItemOptionForItem;
use EDD\Vendor\Square\Models\CatalogObject;
use EDD\Vendor\Square\Models\CatalogObjectCategory;

/**
 * Builder for model CatalogItem
 *
 * @see CatalogItem
 */
class CatalogItemBuilder
{
    /**
     * @var CatalogItem
     */
    private $instance;

    private function __construct(CatalogItem $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Item Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogItem());
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
     * Sets description field.
     *
     * @param string|null $value
     */
    public function description(?string $value): self
    {
        $this->instance->setDescription($value);
        return $this;
    }

    /**
     * Unsets description field.
     */
    public function unsetDescription(): self
    {
        $this->instance->unsetDescription();
        return $this;
    }

    /**
     * Sets abbreviation field.
     *
     * @param string|null $value
     */
    public function abbreviation(?string $value): self
    {
        $this->instance->setAbbreviation($value);
        return $this;
    }

    /**
     * Unsets abbreviation field.
     */
    public function unsetAbbreviation(): self
    {
        $this->instance->unsetAbbreviation();
        return $this;
    }

    /**
     * Sets label color field.
     *
     * @param string|null $value
     */
    public function labelColor(?string $value): self
    {
        $this->instance->setLabelColor($value);
        return $this;
    }

    /**
     * Unsets label color field.
     */
    public function unsetLabelColor(): self
    {
        $this->instance->unsetLabelColor();
        return $this;
    }

    /**
     * Sets is taxable field.
     *
     * @param bool|null $value
     */
    public function isTaxable(?bool $value): self
    {
        $this->instance->setIsTaxable($value);
        return $this;
    }

    /**
     * Unsets is taxable field.
     */
    public function unsetIsTaxable(): self
    {
        $this->instance->unsetIsTaxable();
        return $this;
    }

    /**
     * Sets available online field.
     *
     * @param bool|null $value
     */
    public function availableOnline(?bool $value): self
    {
        $this->instance->setAvailableOnline($value);
        return $this;
    }

    /**
     * Unsets available online field.
     */
    public function unsetAvailableOnline(): self
    {
        $this->instance->unsetAvailableOnline();
        return $this;
    }

    /**
     * Sets available for pickup field.
     *
     * @param bool|null $value
     */
    public function availableForPickup(?bool $value): self
    {
        $this->instance->setAvailableForPickup($value);
        return $this;
    }

    /**
     * Unsets available for pickup field.
     */
    public function unsetAvailableForPickup(): self
    {
        $this->instance->unsetAvailableForPickup();
        return $this;
    }

    /**
     * Sets available electronically field.
     *
     * @param bool|null $value
     */
    public function availableElectronically(?bool $value): self
    {
        $this->instance->setAvailableElectronically($value);
        return $this;
    }

    /**
     * Unsets available electronically field.
     */
    public function unsetAvailableElectronically(): self
    {
        $this->instance->unsetAvailableElectronically();
        return $this;
    }

    /**
     * Sets category id field.
     *
     * @param string|null $value
     */
    public function categoryId(?string $value): self
    {
        $this->instance->setCategoryId($value);
        return $this;
    }

    /**
     * Unsets category id field.
     */
    public function unsetCategoryId(): self
    {
        $this->instance->unsetCategoryId();
        return $this;
    }

    /**
     * Sets tax ids field.
     *
     * @param string[]|null $value
     */
    public function taxIds(?array $value): self
    {
        $this->instance->setTaxIds($value);
        return $this;
    }

    /**
     * Unsets tax ids field.
     */
    public function unsetTaxIds(): self
    {
        $this->instance->unsetTaxIds();
        return $this;
    }

    /**
     * Sets modifier list info field.
     *
     * @param CatalogItemModifierListInfo[]|null $value
     */
    public function modifierListInfo(?array $value): self
    {
        $this->instance->setModifierListInfo($value);
        return $this;
    }

    /**
     * Unsets modifier list info field.
     */
    public function unsetModifierListInfo(): self
    {
        $this->instance->unsetModifierListInfo();
        return $this;
    }

    /**
     * Sets variations field.
     *
     * @param CatalogObject[]|null $value
     */
    public function variations(?array $value): self
    {
        $this->instance->setVariations($value);
        return $this;
    }

    /**
     * Unsets variations field.
     */
    public function unsetVariations(): self
    {
        $this->instance->unsetVariations();
        return $this;
    }

    /**
     * Sets product type field.
     *
     * @param string|null $value
     */
    public function productType(?string $value): self
    {
        $this->instance->setProductType($value);
        return $this;
    }

    /**
     * Sets skip modifier screen field.
     *
     * @param bool|null $value
     */
    public function skipModifierScreen(?bool $value): self
    {
        $this->instance->setSkipModifierScreen($value);
        return $this;
    }

    /**
     * Unsets skip modifier screen field.
     */
    public function unsetSkipModifierScreen(): self
    {
        $this->instance->unsetSkipModifierScreen();
        return $this;
    }

    /**
     * Sets item options field.
     *
     * @param CatalogItemOptionForItem[]|null $value
     */
    public function itemOptions(?array $value): self
    {
        $this->instance->setItemOptions($value);
        return $this;
    }

    /**
     * Unsets item options field.
     */
    public function unsetItemOptions(): self
    {
        $this->instance->unsetItemOptions();
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
     * Sets sort name field.
     *
     * @param string|null $value
     */
    public function sortName(?string $value): self
    {
        $this->instance->setSortName($value);
        return $this;
    }

    /**
     * Unsets sort name field.
     */
    public function unsetSortName(): self
    {
        $this->instance->unsetSortName();
        return $this;
    }

    /**
     * Sets categories field.
     *
     * @param CatalogObjectCategory[]|null $value
     */
    public function categories(?array $value): self
    {
        $this->instance->setCategories($value);
        return $this;
    }

    /**
     * Unsets categories field.
     */
    public function unsetCategories(): self
    {
        $this->instance->unsetCategories();
        return $this;
    }

    /**
     * Sets description html field.
     *
     * @param string|null $value
     */
    public function descriptionHtml(?string $value): self
    {
        $this->instance->setDescriptionHtml($value);
        return $this;
    }

    /**
     * Unsets description html field.
     */
    public function unsetDescriptionHtml(): self
    {
        $this->instance->unsetDescriptionHtml();
        return $this;
    }

    /**
     * Sets description plaintext field.
     *
     * @param string|null $value
     */
    public function descriptionPlaintext(?string $value): self
    {
        $this->instance->setDescriptionPlaintext($value);
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
     * Sets is archived field.
     *
     * @param bool|null $value
     */
    public function isArchived(?bool $value): self
    {
        $this->instance->setIsArchived($value);
        return $this;
    }

    /**
     * Unsets is archived field.
     */
    public function unsetIsArchived(): self
    {
        $this->instance->unsetIsArchived();
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
     * Sets food and beverage details field.
     *
     * @param CatalogItemFoodAndBeverageDetails|null $value
     */
    public function foodAndBeverageDetails(?CatalogItemFoodAndBeverageDetails $value): self
    {
        $this->instance->setFoodAndBeverageDetails($value);
        return $this;
    }

    /**
     * Sets reporting category field.
     *
     * @param CatalogObjectCategory|null $value
     */
    public function reportingCategory(?CatalogObjectCategory $value): self
    {
        $this->instance->setReportingCategory($value);
        return $this;
    }

    /**
     * Initializes a new Catalog Item object.
     */
    public function build(): CatalogItem
    {
        return CoreHelper::clone($this->instance);
    }
}
