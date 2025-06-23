<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerDetails;

/**
 * Builder for model CustomerDetails
 *
 * @see CustomerDetails
 */
class CustomerDetailsBuilder
{
    /**
     * @var CustomerDetails
     */
    private $instance;

    private function __construct(CustomerDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Customer Details Builder object.
     */
    public static function init(): self
    {
        return new self(new CustomerDetails());
    }

    /**
     * Sets customer initiated field.
     *
     * @param bool|null $value
     */
    public function customerInitiated(?bool $value): self
    {
        $this->instance->setCustomerInitiated($value);
        return $this;
    }

    /**
     * Unsets customer initiated field.
     */
    public function unsetCustomerInitiated(): self
    {
        $this->instance->unsetCustomerInitiated();
        return $this;
    }

    /**
     * Sets seller keyed in field.
     *
     * @param bool|null $value
     */
    public function sellerKeyedIn(?bool $value): self
    {
        $this->instance->setSellerKeyedIn($value);
        return $this;
    }

    /**
     * Unsets seller keyed in field.
     */
    public function unsetSellerKeyedIn(): self
    {
        $this->instance->unsetSellerKeyedIn();
        return $this;
    }

    /**
     * Initializes a new Customer Details object.
     */
    public function build(): CustomerDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
