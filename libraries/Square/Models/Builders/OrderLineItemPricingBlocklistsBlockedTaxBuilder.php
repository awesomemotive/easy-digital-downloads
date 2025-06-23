<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\OrderLineItemPricingBlocklistsBlockedTax;

/**
 * Builder for model OrderLineItemPricingBlocklistsBlockedTax
 *
 * @see OrderLineItemPricingBlocklistsBlockedTax
 */
class OrderLineItemPricingBlocklistsBlockedTaxBuilder
{
    /**
     * @var OrderLineItemPricingBlocklistsBlockedTax
     */
    private $instance;

    private function __construct(OrderLineItemPricingBlocklistsBlockedTax $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Line Item Pricing Blocklists Blocked Tax Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderLineItemPricingBlocklistsBlockedTax());
    }

    /**
     * Sets uid field.
     *
     * @param string|null $value
     */
    public function uid(?string $value): self
    {
        $this->instance->setUid($value);
        return $this;
    }

    /**
     * Unsets uid field.
     */
    public function unsetUid(): self
    {
        $this->instance->unsetUid();
        return $this;
    }

    /**
     * Sets tax uid field.
     *
     * @param string|null $value
     */
    public function taxUid(?string $value): self
    {
        $this->instance->setTaxUid($value);
        return $this;
    }

    /**
     * Unsets tax uid field.
     */
    public function unsetTaxUid(): self
    {
        $this->instance->unsetTaxUid();
        return $this;
    }

    /**
     * Sets tax catalog object id field.
     *
     * @param string|null $value
     */
    public function taxCatalogObjectId(?string $value): self
    {
        $this->instance->setTaxCatalogObjectId($value);
        return $this;
    }

    /**
     * Unsets tax catalog object id field.
     */
    public function unsetTaxCatalogObjectId(): self
    {
        $this->instance->unsetTaxCatalogObjectId();
        return $this;
    }

    /**
     * Initializes a new Order Line Item Pricing Blocklists Blocked Tax object.
     */
    public function build(): OrderLineItemPricingBlocklistsBlockedTax
    {
        return CoreHelper::clone($this->instance);
    }
}
