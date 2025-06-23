<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerTaxIds;

/**
 * Builder for model CustomerTaxIds
 *
 * @see CustomerTaxIds
 */
class CustomerTaxIdsBuilder
{
    /**
     * @var CustomerTaxIds
     */
    private $instance;

    private function __construct(CustomerTaxIds $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Customer Tax Ids Builder object.
     */
    public static function init(): self
    {
        return new self(new CustomerTaxIds());
    }

    /**
     * Sets eu vat field.
     *
     * @param string|null $value
     */
    public function euVat(?string $value): self
    {
        $this->instance->setEuVat($value);
        return $this;
    }

    /**
     * Unsets eu vat field.
     */
    public function unsetEuVat(): self
    {
        $this->instance->unsetEuVat();
        return $this;
    }

    /**
     * Initializes a new Customer Tax Ids object.
     */
    public function build(): CustomerTaxIds
    {
        return CoreHelper::clone($this->instance);
    }
}
