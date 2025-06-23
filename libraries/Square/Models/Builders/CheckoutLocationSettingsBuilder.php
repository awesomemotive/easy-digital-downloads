<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CheckoutLocationSettings;
use EDD\Vendor\Square\Models\CheckoutLocationSettingsBranding;
use EDD\Vendor\Square\Models\CheckoutLocationSettingsCoupons;
use EDD\Vendor\Square\Models\CheckoutLocationSettingsPolicy;
use EDD\Vendor\Square\Models\CheckoutLocationSettingsTipping;

/**
 * Builder for model CheckoutLocationSettings
 *
 * @see CheckoutLocationSettings
 */
class CheckoutLocationSettingsBuilder
{
    /**
     * @var CheckoutLocationSettings
     */
    private $instance;

    private function __construct(CheckoutLocationSettings $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Checkout Location Settings Builder object.
     */
    public static function init(): self
    {
        return new self(new CheckoutLocationSettings());
    }

    /**
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Unsets location id field.
     */
    public function unsetLocationId(): self
    {
        $this->instance->unsetLocationId();
        return $this;
    }

    /**
     * Sets customer notes enabled field.
     *
     * @param bool|null $value
     */
    public function customerNotesEnabled(?bool $value): self
    {
        $this->instance->setCustomerNotesEnabled($value);
        return $this;
    }

    /**
     * Unsets customer notes enabled field.
     */
    public function unsetCustomerNotesEnabled(): self
    {
        $this->instance->unsetCustomerNotesEnabled();
        return $this;
    }

    /**
     * Sets policies field.
     *
     * @param CheckoutLocationSettingsPolicy[]|null $value
     */
    public function policies(?array $value): self
    {
        $this->instance->setPolicies($value);
        return $this;
    }

    /**
     * Unsets policies field.
     */
    public function unsetPolicies(): self
    {
        $this->instance->unsetPolicies();
        return $this;
    }

    /**
     * Sets branding field.
     *
     * @param CheckoutLocationSettingsBranding|null $value
     */
    public function branding(?CheckoutLocationSettingsBranding $value): self
    {
        $this->instance->setBranding($value);
        return $this;
    }

    /**
     * Sets tipping field.
     *
     * @param CheckoutLocationSettingsTipping|null $value
     */
    public function tipping(?CheckoutLocationSettingsTipping $value): self
    {
        $this->instance->setTipping($value);
        return $this;
    }

    /**
     * Sets coupons field.
     *
     * @param CheckoutLocationSettingsCoupons|null $value
     */
    public function coupons(?CheckoutLocationSettingsCoupons $value): self
    {
        $this->instance->setCoupons($value);
        return $this;
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
     * Initializes a new Checkout Location Settings object.
     */
    public function build(): CheckoutLocationSettings
    {
        return CoreHelper::clone($this->instance);
    }
}
