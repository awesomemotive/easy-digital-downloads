<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Defines the body parameters that can be included in requests to the
 * [BulkUpdateCustomers]($e/Customers/BulkUpdateCustomers) endpoint.
 */
class BulkUpdateCustomersRequest implements \JsonSerializable
{
    /**
     * @var array<string,BulkUpdateCustomerData>
     */
    private $customers;

    /**
     * @param array<string,BulkUpdateCustomerData> $customers
     */
    public function __construct(array $customers)
    {
        $this->customers = $customers;
    }

    /**
     * Returns Customers.
     * A map of 1 to 100 individual update requests, represented by `customer ID: { customer data }`
     * key-value pairs.
     *
     * Each key is the ID of the [customer profile](entity:Customer) to update. To update a customer
     * profile
     * that was created by merging existing profiles, provide the ID of the newly created profile.
     *
     * Each value contains the updated customer data. Only new or changed fields are required. To add or
     * update a field, specify the new value. To remove a field, specify `null`.
     *
     * @return array<string,BulkUpdateCustomerData>
     */
    public function getCustomers(): array
    {
        return $this->customers;
    }

    /**
     * Sets Customers.
     * A map of 1 to 100 individual update requests, represented by `customer ID: { customer data }`
     * key-value pairs.
     *
     * Each key is the ID of the [customer profile](entity:Customer) to update. To update a customer
     * profile
     * that was created by merging existing profiles, provide the ID of the newly created profile.
     *
     * Each value contains the updated customer data. Only new or changed fields are required. To add or
     * update a field, specify the new value. To remove a field, specify `null`.
     *
     * @required
     * @maps customers
     *
     * @param array<string,BulkUpdateCustomerData> $customers
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
