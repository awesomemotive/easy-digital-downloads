<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Defines the body parameters that can be included in requests to the
 * [BulkCreateCustomers]($e/Customers/BulkCreateCustomers) endpoint.
 */
class BulkCreateCustomersRequest implements \JsonSerializable
{
    /**
     * @var array<string,BulkCreateCustomerData>
     */
    private $customers;

    /**
     * @param array<string,BulkCreateCustomerData> $customers
     */
    public function __construct(array $customers)
    {
        $this->customers = $customers;
    }

    /**
     * Returns Customers.
     * A map of 1 to 100 individual create requests, represented by `idempotency key: { customer data }`
     * key-value pairs.
     *
     * Each key is an [idempotency key](https://developer.squareup.com/docs/build-basics/common-api-
     * patterns/idempotency)
     * that uniquely identifies the create request. Each value contains the customer data used to create
     * the
     * customer profile.
     *
     * @return array<string,BulkCreateCustomerData>
     */
    public function getCustomers(): array
    {
        return $this->customers;
    }

    /**
     * Sets Customers.
     * A map of 1 to 100 individual create requests, represented by `idempotency key: { customer data }`
     * key-value pairs.
     *
     * Each key is an [idempotency key](https://developer.squareup.com/docs/build-basics/common-api-
     * patterns/idempotency)
     * that uniquely identifies the create request. Each value contains the customer data used to create
     * the
     * customer profile.
     *
     * @required
     * @maps customers
     *
     * @param array<string,BulkCreateCustomerData> $customers
     */
    public function setCustomers(array $customers): void
    {
        $this->customers = $customers;
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
        $json['customers'] = $this->customers;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
