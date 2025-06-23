<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Defines the fields that are included in the response body of
 * a request to the `SearchCustomers` endpoint.
 *
 * Either `errors` or `customers` is present in a given response (never both).
 */
class SearchCustomersResponse implements \JsonSerializable
{
    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * @var Customer[]|null
     */
    private $customers;

    /**
     * @var string|null
     */
    private $cursor;

    /**
     * @var int|null
     */
    private $count;

    /**
     * Returns Errors.
     * Any errors that occurred during the request.
     *
     * @return Error[]|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Sets Errors.
     * Any errors that occurred during the request.
     *
     * @maps errors
     *
     * @param Error[]|null $errors
     */
    public function setErrors(?array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * Returns Customers.
     * The customer profiles that match the search query. If any search condition is not met, the result is
     * an empty object (`{}`).
     * Only customer profiles with public information (`given_name`, `family_name`, `company_name`,
     * `email_address`, or `phone_number`)
     * are included in the response.
     *
     * @return Customer[]|null
     */
    public function getCustomers(): ?array
    {
        return $this->customers;
    }

    /**
     * Sets Customers.
     * The customer profiles that match the search query. If any search condition is not met, the result is
     * an empty object (`{}`).
     * Only customer profiles with public information (`given_name`, `family_name`, `company_name`,
     * `email_address`, or `phone_number`)
     * are included in the response.
     *
     * @maps customers
     *
     * @param Customer[]|null $customers
     */
    public function setCustomers(?array $customers): void
    {
        $this->customers = $customers;
    }

    /**
     * Returns Cursor.
     * A pagination cursor that can be used during subsequent calls
     * to `SearchCustomers` to retrieve the next set of results associated
     * with the original query. Pagination cursors are only present when
     * a request succeeds and additional results are available.
     *
     * For more information, see [Pagination](https://developer.squareup.com/docs/build-basics/common-api-
     * patterns/pagination).
     */
    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    /**
     * Sets Cursor.
     * A pagination cursor that can be used during subsequent calls
     * to `SearchCustomers` to retrieve the next set of results associated
     * with the original query. Pagination cursors are only present when
     * a request succeeds and additional results are available.
     *
     * For more information, see [Pagination](https://developer.squareup.com/docs/build-basics/common-api-
     * patterns/pagination).
     *
     * @maps cursor
     */
    public function setCursor(?string $cursor): void
    {
        $this->cursor = $cursor;
    }

    /**
     * Returns Count.
     * The total count of customers associated with the EDD\Vendor\Square account that match the search query. Only
     * customer profiles with
     * public information (`given_name`, `family_name`, `company_name`, `email_address`, or `phone_number`)
     * are counted. This field is
     * present only if `count` is set to `true` in the request.
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * Sets Count.
     * The total count of customers associated with the EDD\Vendor\Square account that match the search query. Only
     * customer profiles with
     * public information (`given_name`, `family_name`, `company_name`, `email_address`, or `phone_number`)
     * are counted. This field is
     * present only if `count` is set to `true` in the request.
     *
     * @maps count
     */
    public function setCount(?int $count): void
    {
        $this->count = $count;
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
        if (isset($this->errors)) {
            $json['errors']    = $this->errors;
        }
        if (isset($this->customers)) {
            $json['customers'] = $this->customers;
        }
        if (isset($this->cursor)) {
            $json['cursor']    = $this->cursor;
        }
        if (isset($this->count)) {
            $json['count']     = $this->count;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
