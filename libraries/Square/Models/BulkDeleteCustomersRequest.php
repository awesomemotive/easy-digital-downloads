<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Defines the body parameters that can be included in requests to the
 * [BulkDeleteCustomers]($e/Customers/BulkDeleteCustomers) endpoint.
 */
class BulkDeleteCustomersRequest implements \JsonSerializable
{
    /**
     * @var string[]
     */
    private $customerIds;

    /**
     * @param string[] $customerIds
     */
    public function __construct(array $customerIds)
    {
        $this->customerIds = $customerIds;
    }

    /**
     * Returns Customer Ids.
     * The IDs of the [customer profiles](entity:Customer) to delete.
     *
     * @return string[]
     */
    public function getCustomerIds(): array
    {
        return $this->customerIds;
    }

    /**
     * Sets Customer Ids.
     * The IDs of the [customer profiles](entity:Customer) to delete.
     *
     * @required
     * @maps customer_ids
     *
     * @param string[] $customerIds
     */
    public function setCustomerIds(array $customerIds): void
    {
        $this->customerIds = $customerIds;
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
        $json['customer_ids'] = $this->customerIds;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
