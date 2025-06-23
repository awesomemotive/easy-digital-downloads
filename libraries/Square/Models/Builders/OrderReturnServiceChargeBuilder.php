<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\OrderLineItemAppliedTax;
use EDD\Vendor\Square\Models\OrderReturnServiceCharge;

/**
 * Builder for model OrderReturnServiceCharge
 *
 * @see OrderReturnServiceCharge
 */
class OrderReturnServiceChargeBuilder
{
    /**
     * @var OrderReturnServiceCharge
     */
    private $instance;

    private function __construct(OrderReturnServiceCharge $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Return Service Charge Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderReturnServiceCharge());
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
     * Sets source service charge uid field.
     *
     * @param string|null $value
     */
    public function sourceServiceChargeUid(?string $value): self
    {
        $this->instance->setSourceServiceChargeUid($value);
        return $this;
    }

    /**
     * Unsets source service charge uid field.
     */
    public function unsetSourceServiceChargeUid(): self
    {
        $this->instance->unsetSourceServiceChargeUid();
        return $this;
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
     * Sets catalog object id field.
     *
     * @param string|null $value
     */
    public function catalogObjectId(?string $value): self
    {
        $this->instance->setCatalogObjectId($value);
        return $this;
    }

    /**
     * Unsets catalog object id field.
     */
    public function unsetCatalogObjectId(): self
    {
        $this->instance->unsetCatalogObjectId();
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
     * Sets applied money field.
     *
     * @param Money|null $value
     */
    public function appliedMoney(?Money $value): self
    {
        $this->instance->setAppliedMoney($value);
        return $this;
    }

    /**
     * Sets total money field.
     *
     * @param Money|null $value
     */
    public function totalMoney(?Money $value): self
    {
        $this->instance->setTotalMoney($value);
        return $this;
    }

    /**
     * Sets total tax money field.
     *
     * @param Money|null $value
     */
    public function totalTaxMoney(?Money $value): self
    {
        $this->instance->setTotalTaxMoney($value);
        return $this;
    }

    /**
     * Sets calculation phase field.
     *
     * @param string|null $value
     */
    public function calculationPhase(?string $value): self
    {
        $this->instance->setCalculationPhase($value);
        return $this;
    }

    /**
     * Sets taxable field.
     *
     * @param bool|null $value
     */
    public function taxable(?bool $value): self
    {
        $this->instance->setTaxable($value);
        return $this;
    }

    /**
     * Unsets taxable field.
     */
    public function unsetTaxable(): self
    {
        $this->instance->unsetTaxable();
        return $this;
    }

    /**
     * Sets applied taxes field.
     *
     * @param OrderLineItemAppliedTax[]|null $value
     */
    public function appliedTaxes(?array $value): self
    {
        $this->instance->setAppliedTaxes($value);
        return $this;
    }

    /**
     * Unsets applied taxes field.
     */
    public function unsetAppliedTaxes(): self
    {
        $this->instance->unsetAppliedTaxes();
        return $this;
    }

    /**
     * Sets treatment type field.
     *
     * @param string|null $value
     */
    public function treatmentType(?string $value): self
    {
        $this->instance->setTreatmentType($value);
        return $this;
    }

    /**
     * Sets scope field.
     *
     * @param string|null $value
     */
    public function scope(?string $value): self
    {
        $this->instance->setScope($value);
        return $this;
    }

    /**
     * Initializes a new Order Return Service Charge object.
     */
    public function build(): OrderReturnServiceCharge
    {
        return CoreHelper::clone($this->instance);
    }
}
