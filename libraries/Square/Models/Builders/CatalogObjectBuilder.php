<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogAvailabilityPeriod;
use EDD\Vendor\Square\Models\CatalogCategory;
use EDD\Vendor\Square\Models\CatalogCustomAttributeDefinition;
use EDD\Vendor\Square\Models\CatalogCustomAttributeValue;
use EDD\Vendor\Square\Models\CatalogDiscount;
use EDD\Vendor\Square\Models\CatalogImage;
use EDD\Vendor\Square\Models\CatalogItem;
use EDD\Vendor\Square\Models\CatalogItemOption;
use EDD\Vendor\Square\Models\CatalogItemOptionValue;
use EDD\Vendor\Square\Models\CatalogItemVariation;
use EDD\Vendor\Square\Models\CatalogMeasurementUnit;
use EDD\Vendor\Square\Models\CatalogModifier;
use EDD\Vendor\Square\Models\CatalogModifierList;
use EDD\Vendor\Square\Models\CatalogObject;
use EDD\Vendor\Square\Models\CatalogPricingRule;
use EDD\Vendor\Square\Models\CatalogProductSet;
use EDD\Vendor\Square\Models\CatalogQuickAmountsSettings;
use EDD\Vendor\Square\Models\CatalogSubscriptionPlan;
use EDD\Vendor\Square\Models\CatalogSubscriptionPlanVariation;
use EDD\Vendor\Square\Models\CatalogTax;
use EDD\Vendor\Square\Models\CatalogTimePeriod;
use EDD\Vendor\Square\Models\CatalogV1Id;

/**
 * Builder for model CatalogObject
 *
 * @see CatalogObject
 */
class CatalogObjectBuilder
{
    /**
     * @var CatalogObject
     */
    private $instance;

    private function __construct(CatalogObject $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Object Builder object.
     *
     * @param string $type
     * @param string $id
     */
    public static function init(string $type, string $id): self
    {
        return new self(new CatalogObject($type, $id));
    }

    /**
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Sets version field.
     *
     * @param int|null $value
     */
    public function version(?int $value): self
    {
        $this->instance->setVersion($value);
        return $this;
    }

    /**
     * Sets is deleted field.
     *
     * @param bool|null $value
     */
    public function isDeleted(?bool $value): self
    {
        $this->instance->setIsDeleted($value);
        return $this;
    }

    /**
     * Unsets is deleted field.
     */
    public function unsetIsDeleted(): self
    {
        $this->instance->unsetIsDeleted();
        return $this;
    }

    /**
     * Sets custom attribute values field.
     *
     * @param array<string,CatalogCustomAttributeValue>|null $value
     */
    public function customAttributeValues(?array $value): self
    {
        $this->instance->setCustomAttributeValues($value);
        return $this;
    }

    /**
     * Unsets custom attribute values field.
     */
    public function unsetCustomAttributeValues(): self
    {
        $this->instance->unsetCustomAttributeValues();
        return $this;
    }

    /**
     * Sets catalog v 1 ids field.
     *
     * @param CatalogV1Id[]|null $value
     */
    public function catalogV1Ids(?array $value): self
    {
        $this->instance->setCatalogV1Ids($value);
        return $this;
    }

    /**
     * Unsets catalog v 1 ids field.
     */
    public function unsetCatalogV1Ids(): self
    {
        $this->instance->unsetCatalogV1Ids();
        return $this;
    }

    /**
     * Sets present at all locations field.
     *
     * @param bool|null $value
     */
    public function presentAtAllLocations(?bool $value): self
    {
        $this->instance->setPresentAtAllLocations($value);
        return $this;
    }

    /**
     * Unsets present at all locations field.
     */
    public function unsetPresentAtAllLocations(): self
    {
        $this->instance->unsetPresentAtAllLocations();
        return $this;
    }

    /**
     * Sets present at location ids field.
     *
     * @param string[]|null $value
     */
    public function presentAtLocationIds(?array $value): self
    {
        $this->instance->setPresentAtLocationIds($value);
        return $this;
    }

    /**
     * Unsets present at location ids field.
     */
    public function unsetPresentAtLocationIds(): self
    {
        $this->instance->unsetPresentAtLocationIds();
        return $this;
    }

    /**
     * Sets absent at location ids field.
     *
     * @param string[]|null $value
     */
    public function absentAtLocationIds(?array $value): self
    {
        $this->instance->setAbsentAtLocationIds($value);
        return $this;
    }

    /**
     * Unsets absent at location ids field.
     */
    public function unsetAbsentAtLocationIds(): self
    {
        $this->instance->unsetAbsentAtLocationIds();
        return $this;
    }

    /**
     * Sets item data field.
     *
     * @param CatalogItem|null $value
     */
    public function itemData(?CatalogItem $value): self
    {
        $this->instance->setItemData($value);
        return $this;
    }

    /**
     * Sets category data field.
     *
     * @param CatalogCategory|null $value
     */
    public function categoryData(?CatalogCategory $value): self
    {
        $this->instance->setCategoryData($value);
        return $this;
    }

    /**
     * Sets item variation data field.
     *
     * @param CatalogItemVariation|null $value
     */
    public function itemVariationData(?CatalogItemVariation $value): self
    {
        $this->instance->setItemVariationData($value);
        return $this;
    }

    /**
     * Sets tax data field.
     *
     * @param CatalogTax|null $value
     */
    public function taxData(?CatalogTax $value): self
    {
        $this->instance->setTaxData($value);
        return $this;
    }

    /**
     * Sets discount data field.
     *
     * @param CatalogDiscount|null $value
     */
    public function discountData(?CatalogDiscount $value): self
    {
        $this->instance->setDiscountData($value);
        return $this;
    }

    /**
     * Sets modifier list data field.
     *
     * @param CatalogModifierList|null $value
     */
    public function modifierListData(?CatalogModifierList $value): self
    {
        $this->instance->setModifierListData($value);
        return $this;
    }

    /**
     * Sets modifier data field.
     *
     * @param CatalogModifier|null $value
     */
    public function modifierData(?CatalogModifier $value): self
    {
        $this->instance->setModifierData($value);
        return $this;
    }

    /**
     * Sets time period data field.
     *
     * @param CatalogTimePeriod|null $value
     */
    public function timePeriodData(?CatalogTimePeriod $value): self
    {
        $this->instance->setTimePeriodData($value);
        return $this;
    }

    /**
     * Sets product set data field.
     *
     * @param CatalogProductSet|null $value
     */
    public function productSetData(?CatalogProductSet $value): self
    {
        $this->instance->setProductSetData($value);
        return $this;
    }

    /**
     * Sets pricing rule data field.
     *
     * @param CatalogPricingRule|null $value
     */
    public function pricingRuleData(?CatalogPricingRule $value): self
    {
        $this->instance->setPricingRuleData($value);
        return $this;
    }

    /**
     * Sets image data field.
     *
     * @param CatalogImage|null $value
     */
    public function imageData(?CatalogImage $value): self
    {
        $this->instance->setImageData($value);
        return $this;
    }

    /**
     * Sets measurement unit data field.
     *
     * @param CatalogMeasurementUnit|null $value
     */
    public function measurementUnitData(?CatalogMeasurementUnit $value): self
    {
        $this->instance->setMeasurementUnitData($value);
        return $this;
    }

    /**
     * Sets subscription plan data field.
     *
     * @param CatalogSubscriptionPlan|null $value
     */
    public function subscriptionPlanData(?CatalogSubscriptionPlan $value): self
    {
        $this->instance->setSubscriptionPlanData($value);
        return $this;
    }

    /**
     * Sets item option data field.
     *
     * @param CatalogItemOption|null $value
     */
    public function itemOptionData(?CatalogItemOption $value): self
    {
        $this->instance->setItemOptionData($value);
        return $this;
    }

    /**
     * Sets item option value data field.
     *
     * @param CatalogItemOptionValue|null $value
     */
    public function itemOptionValueData(?CatalogItemOptionValue $value): self
    {
        $this->instance->setItemOptionValueData($value);
        return $this;
    }

    /**
     * Sets custom attribute definition data field.
     *
     * @param CatalogCustomAttributeDefinition|null $value
     */
    public function customAttributeDefinitionData(?CatalogCustomAttributeDefinition $value): self
    {
        $this->instance->setCustomAttributeDefinitionData($value);
        return $this;
    }

    /**
     * Sets quick amounts settings data field.
     *
     * @param CatalogQuickAmountsSettings|null $value
     */
    public function quickAmountsSettingsData(?CatalogQuickAmountsSettings $value): self
    {
        $this->instance->setQuickAmountsSettingsData($value);
        return $this;
    }

    /**
     * Sets subscription plan variation data field.
     *
     * @param CatalogSubscriptionPlanVariation|null $value
     */
    public function subscriptionPlanVariationData(?CatalogSubscriptionPlanVariation $value): self
    {
        $this->instance->setSubscriptionPlanVariationData($value);
        return $this;
    }

    /**
     * Sets availability period data field.
     *
     * @param CatalogAvailabilityPeriod|null $value
     */
    public function availabilityPeriodData(?CatalogAvailabilityPeriod $value): self
    {
        $this->instance->setAvailabilityPeriodData($value);
        return $this;
    }

    /**
     * Initializes a new Catalog Object object.
     */
    public function build(): CatalogObject
    {
        return CoreHelper::clone($this->instance);
    }
}
