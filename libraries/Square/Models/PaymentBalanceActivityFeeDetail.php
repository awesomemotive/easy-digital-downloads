<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class PaymentBalanceActivityFeeDetail implements \JsonSerializable
{
    /**
     * @var array
     */
    private $paymentId = [];

    /**
     * Returns Payment Id.
     * The ID of the payment associated with this activity
     * This will only be populated when a principal LedgerEntryToken is also populated.
     * If the fee is independent (there is no principal LedgerEntryToken) then this will likely not
     * be populated.
     */
    public function getPaymentId(): ?string
    {
        if (count($this->paymentId) == 0) {
            return null;
        }
        return $this->paymentId['value'];
    }

    /**
     * Sets Payment Id.
     * The ID of the payment associated with this activity
     * This will only be populated when a principal LedgerEntryToken is also populated.
     * If the fee is independent (there is no principal LedgerEntryToken) then this will likely not
     * be populated.
     *
     * @maps payment_id
     */
    public function setPaymentId(?string $paymentId): void
    {
        $this->paymentId['value'] = $paymentId;
    }

    /**
     * Unsets Payment Id.
     * The ID of the payment associated with this activity
     * This will only be populated when a principal LedgerEntryToken is also populated.
     * If the fee is independent (there is no principal LedgerEntryToken) then this will likely not
     * be populated.
     */
    public function unsetPaymentId(): void
    {
        $this->paymentId = [];
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
        if (!empty($this->paymentId)) {
            $json['payment_id'] = $this->paymentId['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
