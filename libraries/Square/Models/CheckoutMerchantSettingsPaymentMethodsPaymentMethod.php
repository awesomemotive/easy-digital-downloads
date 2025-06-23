<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * The settings allowed for a payment method.
 */
class CheckoutMerchantSettingsPaymentMethodsPaymentMethod implements \JsonSerializable
{
    /**
     * @var array
     */
    private $enabled = [];

    /**
     * Returns Enabled.
     * Indicates whether the payment method is enabled for the account.
     */
    public function getEnabled(): ?bool
    {
        if (count($this->enabled) == 0) {
            return null;
        }
        return $this->enabled['value'];
    }

    /**
     * Sets Enabled.
     * Indicates whether the payment method is enabled for the account.
     *
     * @maps enabled
     */
    public function setEnabled(?bool $enabled): void
    {
        $this->enabled['value'] = $enabled;
    }

    /**
     * Unsets Enabled.
     * Indicates whether the payment method is enabled for the account.
     */
    public function unsetEnabled(): void
    {
        $this->enabled = [];
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
        if (!empty($this->enabled)) {
            $json['enabled'] = $this->enabled['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
