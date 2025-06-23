<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogTax;

/**
 * Builder for model CatalogTax
 *
 * @see CatalogTax
 */
class CatalogTaxBuilder
{
    /**
     * @var CatalogTax
     */
    private $instance;

    private function __construct(CatalogTax $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Tax Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogTax());
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
     * Sets inclusion type field.
     *
     * @param string|null $value
     */
    public function inclusionType(?string $value): self
    {
        $this->instance->setInclusionType($value);
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
     * Sets applies to custom amounts field.
     *
     * @param bool|null $value
     */
    public function appliesToCustomAmounts(?bool $value): self
    {
        $this->instance->setAppliesToCustomAmounts($value);
        return $this;
    }

    /**
     * Unsets applies to custom amounts field.
     */
    public function unsetAppliesToCustomAmounts(): self
    {
        $this->instance->unsetAppliesToCustomAmounts();
        return $this;
    }

    /**
     * Sets enabled field.
     *
     * @param bool|null $value
     */
    public function enabled(?bool $value): self
    {
        $this->instance->setEnabled($value);
        return $this;
    }

    /**
     * Unsets enabled field.
     */
    public function unsetEnabled(): self
    {
        $this->instance->unsetEnabled();
        return $this;
    }

    /**
     * Sets applies to product set id field.
     *
     * @param string|null $value
     */
    public function appliesToProductSetId(?string $value): self
    {
        $this->instance->setAppliesToProductSetId($value);
        return $this;
    }

    /**
     * Unsets applies to product set id field.
     */
    public function unsetAppliesToProductSetId(): self
    {
        $this->instance->unsetAppliesToProductSetId();
        return $this;
    }

    /**
     * Initializes a new Catalog Tax object.
     */
    public function build(): CatalogTax
    {
        return CoreHelper::clone($this->instance);
    }
}
