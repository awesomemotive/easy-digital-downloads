<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * The settings allowed for AfterpayClearpay.
 */
class CheckoutMerchantSettingsPaymentMethodsAfterpayClearpay implements \JsonSerializable
{
    /**
     * @var CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange|null
     */
    private $orderEligibilityRange;

    /**
     * @var CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange|null
     */
    private $itemEligibilityRange;

    /**
     * @var bool|null
     */
    private $enabled;

    /**
     * Returns Order Eligibility Range.
     * A range of purchase price that qualifies.
     */
    public function getOrderEligibilityRange(): ?CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange
    {
        return $this->orderEligibilityRange;
    }

    /**
     * Sets Order Eligibility Range.
     * A range of purchase price that qualifies.
     *
     * @maps order_eligibility_range
     */
    public function setOrderEligibilityRange(
        ?CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange $orderEligibilityRange
    ): void {
        $this->orderEligibilityRange = $orderEligibilityRange;
    }

    /**
     * Returns Item Eligibility Range.
     * A range of purchase price that qualifies.
     */
    public function getItemEligibilityRange(): ?CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange
    {
        return $this->itemEligibilityRange;
    }

    /**
     * Sets Item Eligibility Range.
     * A range of purchase price that qualifies.
     *
     * @maps item_eligibility_range
     */
    public function setItemEligibilityRange(
        ?CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange $itemEligibilityRange
    ): void {
        $this->itemEligibilityRange = $itemEligibilityRange;
    }

    /**
     * Returns Enabled.
     * Indicates whether the payment method is enabled for the account.
     */
    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * Sets Enabled.
     * Indicates whether the payment method is enabled for the account.
     *
     * @maps enabled
     */
    public function setEnabled(?bool $enabled): void
    {
        $this->enabled = $enabled;
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
        if (isset($this->orderEligibilityRange)) {
            $json['order_eligibility_range'] = $this->orderEligibilityRange;
        }
        if (isset($this->itemEligibilityRange)) {
            $json['item_eligibility_range']  = $this->itemEligibilityRange;
        }
        if (isset($this->enabled)) {
            $json['enabled']                 = $this->enabled;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
