<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogDiscount;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model CatalogDiscount
 *
 * @see CatalogDiscount
 */
class CatalogDiscountBuilder
{
    /**
     * @var CatalogDiscount
     */
    private $instance;

    private function __construct(CatalogDiscount $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Discount Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogDiscount());
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
     * Sets discount type field.
     *
     * @param string|null $value
     */
    public function discountType(?string $value): self
    {
        $this->instance->setDiscountType($value);
        return $this;
    }

    /**
     * Sets percentage field.
     *
     * @param string|null $value
     */
    public function percentage(?string $value): self
    {
        $this->instance->setPercentage($value);
        return $this;
    }

    /**
     * Unsets percentage field.
     */
    public function unsetPercentage(): self
    {
        $this->instance->unsetPercentage();
        return $this;
    }

    /**
     * Sets amount money field.
     *
     * @param Money|null $value
     */
    public function amountMoney(?Money $value): self
    {
        $this->instance->setAmountMoney($value);
        return $this;
    }

    /**
     * Sets pin required field.
     *
     * @param bool|null $value
     */
    public function pinRequired(?bool $value): self
    {
        $this->instance->setPinRequired($value);
        return $this;
    }

    /**
     * Unsets pin required field.
     */
    public function unsetPinRequired(): self
    {
        $this->instance->unsetPinRequired();
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
     * Sets modify tax basis field.
     *
     * @param string|null $value
     */
    public function modifyTaxBasis(?string $value): self
    {
        $this->instance->setModifyTaxBasis($value);
        return $this;
    }

    /**
     * Sets maximum amount money field.
     *
     * @param Money|null $value
     */
    public function maximumAmountMoney(?Money $value): self
    {
        $this->instance->setMaximumAmountMoney($value);
        return $this;
    }

    /**
     * Initializes a new Catalog Discount object.
     */
    public function build(): CatalogDiscount
    {
        return CoreHelper::clone($this->instance);
    }
}
