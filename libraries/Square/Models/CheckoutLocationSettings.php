<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class CheckoutLocationSettings implements \JsonSerializable
{
    /**
     * @var array
     */
    private $locationId = [];

    /**
     * @var array
     */
    private $customerNotesEnabled = [];

    /**
     * @var array
     */
    private $policies = [];

    /**
     * @var CheckoutLocationSettingsBranding|null
     */
    private $branding;

    /**
     * @var CheckoutLocationSettingsTipping|null
     */
    private $tipping;

    /**
     * @var CheckoutLocationSettingsCoupons|null
     */
    private $coupons;

    /**
     * @var string|null
     */
    private $updatedAt;

    /**
     * Returns Location Id.
     * The ID of the location that these settings apply to.
     */
    public function getLocationId(): ?string
    {
        if (count($this->locationId) == 0) {
            return null;
        }
        return $this->locationId['value'];
    }

    /**
     * Sets Location Id.
     * The ID of the location that these settings apply to.
     *
     * @maps location_id
     */
    public function setLocationId(?string $locationId): void
    {
        $this->locationId['value'] = $locationId;
    }

    /**
     * Unsets Location Id.
     * The ID of the location that these settings apply to.
     */
    public function unsetLocationId(): void
    {
        $this->locationId = [];
    }

    /**
     * Returns Customer Notes Enabled.
     * Indicates whether customers are allowed to leave notes at checkout.
     */
    public function getCustomerNotesEnabled(): ?bool
    {
        if (count($this->customerNotesEnabled) == 0) {
            return null;
        }
        return $this->customerNotesEnabled['value'];
    }

    /**
     * Sets Customer Notes Enabled.
     * Indicates whether customers are allowed to leave notes at checkout.
     *
     * @maps customer_notes_enabled
     */
    public function setCustomerNotesEnabled(?bool $customerNotesEnabled): void
    {
        $this->customerNotesEnabled['value'] = $customerNotesEnabled;
    }

    /**
     * Unsets Customer Notes Enabled.
     * Indicates whether customers are allowed to leave notes at checkout.
     */
    public function unsetCustomerNotesEnabled(): void
    {
        $this->customerNotesEnabled = [];
    }

    /**
     * Returns Policies.
     * Policy information is displayed at the bottom of the checkout pages.
     * You can set a maximum of two policies.
     *
     * @return CheckoutLocationSettingsPolicy[]|null
     */
    public function getPolicies(): ?array
    {
        if (count($this->policies) == 0) {
            return null;
        }
        return $this->policies['value'];
    }

    /**
     * Sets Policies.
     * Policy information is displayed at the bottom of the checkout pages.
     * You can set a maximum of two policies.
     *
     * @maps policies
     *
     * @param CheckoutLocationSettingsPolicy[]|null $policies
     */
    public function setPolicies(?array $policies): void
    {
        $this->policies['value'] = $policies;
    }

    /**
     * Unsets Policies.
     * Policy information is displayed at the bottom of the checkout pages.
     * You can set a maximum of two policies.
     */
    public function unsetPolicies(): void
    {
        $this->policies = [];
    }

    /**
     * Returns Branding.
     */
    public function getBranding(): ?CheckoutLocationSettingsBranding
    {
        return $this->branding;
    }

    /**
     * Sets Branding.
     *
     * @maps branding
     */
    public function setBranding(?CheckoutLocationSettingsBranding $branding): void
    {
        $this->branding = $branding;
    }

    /**
     * Returns Tipping.
     */
    public function getTipping(): ?CheckoutLocationSettingsTipping
    {
        return $this->tipping;
    }

    /**
     * Sets Tipping.
     *
     * @maps tipping
     */
    public function setTipping(?CheckoutLocationSettingsTipping $tipping): void
    {
        $this->tipping = $tipping;
    }

    /**
     * Returns Coupons.
     */
    public function getCoupons(): ?CheckoutLocationSettingsCoupons
    {
        return $this->coupons;
    }

    /**
     * Sets Coupons.
     *
     * @maps coupons
     */
    public function setCoupons(?CheckoutLocationSettingsCoupons $coupons): void
    {
        $this->coupons = $coupons;
    }

    /**
     * Returns Updated At.
     * The timestamp when the settings were last updated, in RFC 3339 format.
     * Examples for January 25th, 2020 6:25:34pm Pacific Standard Time:
     * UTC: 2020-01-26T02:25:34Z
     * Pacific Standard Time with UTC offset: 2020-01-25T18:25:34-08:00
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Sets Updated At.
     * The timestamp when the settings were last updated, in RFC 3339 format.
     * Examples for January 25th, 2020 6:25:34pm Pacific Standard Time:
     * UTC: 2020-01-26T02:25:34Z
     * Pacific Standard Time with UTC offset: 2020-01-25T18:25:34-08:00
     *
     * @maps updated_at
     */
    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
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
        if (!empty($this->locationId)) {
            $json['location_id']            = $this->locationId['value'];
        }
        if (!empty($this->customerNotesEnabled)) {
            $json['customer_notes_enabled'] = $this->customerNotesEnabled['value'];
        }
        if (!empty($this->policies)) {
            $json['policies']               = $this->policies['value'];
        }
        if (isset($this->branding)) {
            $json['branding']               = $this->branding;
        }
        if (isset($this->tipping)) {
            $json['tipping']                = $this->tipping;
        }
        if (isset($this->coupons)) {
            $json['coupons']                = $this->coupons;
        }
        if (isset($this->updatedAt)) {
            $json['updated_at']             = $this->updatedAt;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
