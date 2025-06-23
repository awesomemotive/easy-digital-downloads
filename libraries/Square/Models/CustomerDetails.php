<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Details about the customer making the payment.
 */
class CustomerDetails implements \JsonSerializable
{
    /**
     * @var array
     */
    private $customerInitiated = [];

    /**
     * @var array
     */
    private $sellerKeyedIn = [];

    /**
     * Returns Customer Initiated.
     * Indicates whether the customer initiated the payment.
     */
    public function getCustomerInitiated(): ?bool
    {
        if (count($this->customerInitiated) == 0) {
            return null;
        }
        return $this->customerInitiated['value'];
    }

    /**
     * Sets Customer Initiated.
     * Indicates whether the customer initiated the payment.
     *
     * @maps customer_initiated
     */
    public function setCustomerInitiated(?bool $customerInitiated): void
    {
        $this->customerInitiated['value'] = $customerInitiated;
    }

    /**
     * Unsets Customer Initiated.
     * Indicates whether the customer initiated the payment.
     */
    public function unsetCustomerInitiated(): void
    {
        $this->customerInitiated = [];
    }

    /**
     * Returns Seller Keyed In.
     * Indicates that the seller keyed in payment details on behalf of the customer.
     * This is used to flag a payment as Mail Order / Telephone Order (MOTO).
     */
    public function getSellerKeyedIn(): ?bool
    {
        if (count($this->sellerKeyedIn) == 0) {
            return null;
        }
        return $this->sellerKeyedIn['value'];
    }

    /**
     * Sets Seller Keyed In.
     * Indicates that the seller keyed in payment details on behalf of the customer.
     * This is used to flag a payment as Mail Order / Telephone Order (MOTO).
     *
     * @maps seller_keyed_in
     */
    public function setSellerKeyedIn(?bool $sellerKeyedIn): void
    {
        $this->sellerKeyedIn['value'] = $sellerKeyedIn;
    }

    /**
     * Unsets Seller Keyed In.
     * Indicates that the seller keyed in payment details on behalf of the customer.
     * This is used to flag a payment as Mail Order / Telephone Order (MOTO).
     */
    public function unsetSellerKeyedIn(): void
    {
        $this->sellerKeyedIn = [];
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
        if (!empty($this->customerInitiated)) {
            $json['customer_initiated'] = $this->customerInitiated['value'];
        }
        if (!empty($this->sellerKeyedIn)) {
            $json['seller_keyed_in']    = $this->sellerKeyedIn['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
