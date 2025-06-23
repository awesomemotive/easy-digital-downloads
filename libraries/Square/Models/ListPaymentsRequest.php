<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Describes a request to list payments using
 * [ListPayments]($e/Payments/ListPayments).
 *
 * The maximum results per page is 100.
 */
class ListPaymentsRequest implements \JsonSerializable
{
    /**
     * @var array
     */
    private $beginTime = [];

    /**
     * @var array
     */
    private $endTime = [];

    /**
     * @var array
     */
    private $sortOrder = [];

    /**
     * @var array
     */
    private $cursor = [];

    /**
     * @var array
     */
    private $locationId = [];

    /**
     * @var array
     */
    private $total = [];

    /**
     * @var array
     */
    private $last4 = [];

    /**
     * @var array
     */
    private $cardBrand = [];

    /**
     * @var array
     */
    private $limit = [];

    /**
     * @var array
     */
    private $isOfflinePayment = [];

    /**
     * @var array
     */
    private $offlineBeginTime = [];

    /**
     * @var array
     */
    private $offlineEndTime = [];

    /**
     * @var array
     */
    private $updatedAtBeginTime = [];

    /**
     * @var array
     */
    private $updatedAtEndTime = [];

    /**
     * @var string|null
     */
    private $sortField;

    /**
     * Returns Begin Time.
     * Indicates the start of the time range to retrieve payments for, in RFC 3339 format.
     * The range is determined using the `created_at` field for each Payment.
     * Inclusive. Default: The current time minus one year.
     */
    public function getBeginTime(): ?string
    {
        if (count($this->beginTime) == 0) {
            return null;
        }
        return $this->beginTime['value'];
    }

    /**
     * Sets Begin Time.
     * Indicates the start of the time range to retrieve payments for, in RFC 3339 format.
     * The range is determined using the `created_at` field for each Payment.
     * Inclusive. Default: The current time minus one year.
     *
     * @maps begin_time
     */
    public function setBeginTime(?string $beginTime): void
    {
        $this->beginTime['value'] = $beginTime;
    }

    /**
     * Unsets Begin Time.
     * Indicates the start of the time range to retrieve payments for, in RFC 3339 format.
     * The range is determined using the `created_at` field for each Payment.
     * Inclusive. Default: The current time minus one year.
     */
    public function unsetBeginTime(): void
    {
        $this->beginTime = [];
    }

    /**
     * Returns End Time.
     * Indicates the end of the time range to retrieve payments for, in RFC 3339 format.  The
     * range is determined using the `created_at` field for each Payment.
     *
     * Default: The current time.
     */
    public function getEndTime(): ?string
    {
        if (count($this->endTime) == 0) {
            return null;
        }
        return $this->endTime['value'];
    }

    /**
     * Sets End Time.
     * Indicates the end of the time range to retrieve payments for, in RFC 3339 format.  The
     * range is determined using the `created_at` field for each Payment.
     *
     * Default: The current time.
     *
     * @maps end_time
     */
    public function setEndTime(?string $endTime): void
    {
        $this->endTime['value'] = $endTime;
    }

    /**
     * Unsets End Time.
     * Indicates the end of the time range to retrieve payments for, in RFC 3339 format.  The
     * range is determined using the `created_at` field for each Payment.
     *
     * Default: The current time.
     */
    public function unsetEndTime(): void
    {
        $this->endTime = [];
    }

    /**
     * Returns Sort Order.
     * The order in which results are listed by `ListPaymentsRequest.sort_field`:
     * - `ASC` - Oldest to newest.
     * - `DESC` - Newest to oldest (default).
     */
    public function getSortOrder(): ?string
    {
        if (count($this->sortOrder) == 0) {
            return null;
        }
        return $this->sortOrder['value'];
    }

    /**
     * Sets Sort Order.
     * The order in which results are listed by `ListPaymentsRequest.sort_field`:
     * - `ASC` - Oldest to newest.
     * - `DESC` - Newest to oldest (default).
     *
     * @maps sort_order
     */
    public function setSortOrder(?string $sortOrder): void
    {
        $this->sortOrder['value'] = $sortOrder;
    }

    /**
     * Unsets Sort Order.
     * The order in which results are listed by `ListPaymentsRequest.sort_field`:
     * - `ASC` - Oldest to newest.
     * - `DESC` - Newest to oldest (default).
     */
    public function unsetSortOrder(): void
    {
        $this->sortOrder = [];
    }

    /**
     * Returns Cursor.
     * A pagination cursor returned by a previous call to this endpoint.
     * Provide this cursor to retrieve the next set of results for the original query.
     *
     * For more information, see [Pagination](https://developer.squareup.com/docs/build-basics/common-api-
     * patterns/pagination).
     */
    public function getCursor(): ?string
    {
        if (count($this->cursor) == 0) {
            return null;
        }
        return $this->cursor['value'];
    }

    /**
     * Sets Cursor.
     * A pagination cursor returned by a previous call to this endpoint.
     * Provide this cursor to retrieve the next set of results for the original query.
     *
     * For more information, see [Pagination](https://developer.squareup.com/docs/build-basics/common-api-
     * patterns/pagination).
     *
     * @maps cursor
     */
    public function setCursor(?string $cursor): void
    {
        $this->cursor['value'] = $cursor;
    }

    /**
     * Unsets Cursor.
     * A pagination cursor returned by a previous call to this endpoint.
     * Provide this cursor to retrieve the next set of results for the original query.
     *
     * For more information, see [Pagination](https://developer.squareup.com/docs/build-basics/common-api-
     * patterns/pagination).
     */
    public function unsetCursor(): void
    {
        $this->cursor = [];
    }

    /**
     * Returns Location Id.
     * Limit results to the location supplied. By default, results are returned
     * for the default (main) location associated with the seller.
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
     * Limit results to the location supplied. By default, results are returned
     * for the default (main) location associated with the seller.
     *
     * @maps location_id
     */
    public function setLocationId(?string $locationId): void
    {
        $this->locationId['value'] = $locationId;
    }

    /**
     * Unsets Location Id.
     * Limit results to the location supplied. By default, results are returned
     * for the default (main) location associated with the seller.
     */
    public function unsetLocationId(): void
    {
        $this->locationId = [];
    }

    /**
     * Returns Total.
     * The exact amount in the `total_money` for a payment.
     */
    public function getTotal(): ?int
    {
        if (count($this->total) == 0) {
            return null;
        }
        return $this->total['value'];
    }

    /**
     * Sets Total.
     * The exact amount in the `total_money` for a payment.
     *
     * @maps total
     */
    public function setTotal(?int $total): void
    {
        $this->total['value'] = $total;
    }

    /**
     * Unsets Total.
     * The exact amount in the `total_money` for a payment.
     */
    public function unsetTotal(): void
    {
        $this->total = [];
    }

    /**
     * Returns Last 4.
     * The last four digits of a payment card.
     */
    public function getLast4(): ?string
    {
        if (count($this->last4) == 0) {
            return null;
        }
        return $this->last4['value'];
    }

    /**
     * Sets Last 4.
     * The last four digits of a payment card.
     *
     * @maps last_4
     */
    public function setLast4(?string $last4): void
    {
        $this->last4['value'] = $last4;
    }

    /**
     * Unsets Last 4.
     * The last four digits of a payment card.
     */
    public function unsetLast4(): void
    {
        $this->last4 = [];
    }

    /**
     * Returns Card Brand.
     * The brand of the payment card (for example, VISA).
     */
    public function getCardBrand(): ?string
    {
        if (count($this->cardBrand) == 0) {
            return null;
        }
        return $this->cardBrand['value'];
    }

    /**
     * Sets Card Brand.
     * The brand of the payment card (for example, VISA).
     *
     * @maps card_brand
     */
    public function setCardBrand(?string $cardBrand): void
    {
        $this->cardBrand['value'] = $cardBrand;
    }

    /**
     * Unsets Card Brand.
     * The brand of the payment card (for example, VISA).
     */
    public function unsetCardBrand(): void
    {
        $this->cardBrand = [];
    }

    /**
     * Returns Limit.
     * The maximum number of results to be returned in a single page.
     * It is possible to receive fewer results than the specified limit on a given page.
     *
     * The default value of 100 is also the maximum allowed value. If the provided value is
     * greater than 100, it is ignored and the default value is used instead.
     *
     * Default: `100`
     */
    public function getLimit(): ?int
    {
        if (count($this->limit) == 0) {
            return null;
        }
        return $this->limit['value'];
    }

    /**
     * Sets Limit.
     * The maximum number of results to be returned in a single page.
     * It is possible to receive fewer results than the specified limit on a given page.
     *
     * The default value of 100 is also the maximum allowed value. If the provided value is
     * greater than 100, it is ignored and the default value is used instead.
     *
     * Default: `100`
     *
     * @maps limit
     */
    public function setLimit(?int $limit): void
    {
        $this->limit['value'] = $limit;
    }

    /**
     * Unsets Limit.
     * The maximum number of results to be returned in a single page.
     * It is possible to receive fewer results than the specified limit on a given page.
     *
     * The default value of 100 is also the maximum allowed value. If the provided value is
     * greater than 100, it is ignored and the default value is used instead.
     *
     * Default: `100`
     */
    public function unsetLimit(): void
    {
        $this->limit = [];
    }

    /**
     * Returns Is Offline Payment.
     * Whether the payment was taken offline or not.
     */
    public function getIsOfflinePayment(): ?bool
    {
        if (count($this->isOfflinePayment) == 0) {
            return null;
        }
        return $this->isOfflinePayment['value'];
    }

    /**
     * Sets Is Offline Payment.
     * Whether the payment was taken offline or not.
     *
     * @maps is_offline_payment
     */
    public function setIsOfflinePayment(?bool $isOfflinePayment): void
    {
        $this->isOfflinePayment['value'] = $isOfflinePayment;
    }

    /**
     * Unsets Is Offline Payment.
     * Whether the payment was taken offline or not.
     */
    public function unsetIsOfflinePayment(): void
    {
        $this->isOfflinePayment = [];
    }

    /**
     * Returns Offline Begin Time.
     * Indicates the start of the time range for which to retrieve offline payments, in RFC 3339
     * format for timestamps. The range is determined using the
     * `offline_payment_details.client_created_at` field for each Payment. If set, payments without a
     * value set in `offline_payment_details.client_created_at` will not be returned.
     *
     * Default: The current time.
     */
    public function getOfflineBeginTime(): ?string
    {
        if (count($this->offlineBeginTime) == 0) {
            return null;
        }
        return $this->offlineBeginTime['value'];
    }

    /**
     * Sets Offline Begin Time.
     * Indicates the start of the time range for which to retrieve offline payments, in RFC 3339
     * format for timestamps. The range is determined using the
     * `offline_payment_details.client_created_at` field for each Payment. If set, payments without a
     * value set in `offline_payment_details.client_created_at` will not be returned.
     *
     * Default: The current time.
     *
     * @maps offline_begin_time
     */
    public function setOfflineBeginTime(?string $offlineBeginTime): void
    {
        $this->offlineBeginTime['value'] = $offlineBeginTime;
    }

    /**
     * Unsets Offline Begin Time.
     * Indicates the start of the time range for which to retrieve offline payments, in RFC 3339
     * format for timestamps. The range is determined using the
     * `offline_payment_details.client_created_at` field for each Payment. If set, payments without a
     * value set in `offline_payment_details.client_created_at` will not be returned.
     *
     * Default: The current time.
     */
    public function unsetOfflineBeginTime(): void
    {
        $this->offlineBeginTime = [];
    }

    /**
     * Returns Offline End Time.
     * Indicates the end of the time range for which to retrieve offline payments, in RFC 3339
     * format for timestamps. The range is determined using the
     * `offline_payment_details.client_created_at` field for each Payment. If set, payments without a
     * value set in `offline_payment_details.client_created_at` will not be returned.
     *
     * Default: The current time.
     */
    public function getOfflineEndTime(): ?string
    {
        if (count($this->offlineEndTime) == 0) {
            return null;
        }
        return $this->offlineEndTime['value'];
    }

    /**
     * Sets Offline End Time.
     * Indicates the end of the time range for which to retrieve offline payments, in RFC 3339
     * format for timestamps. The range is determined using the
     * `offline_payment_details.client_created_at` field for each Payment. If set, payments without a
     * value set in `offline_payment_details.client_created_at` will not be returned.
     *
     * Default: The current time.
     *
     * @maps offline_end_time
     */
    public function setOfflineEndTime(?string $offlineEndTime): void
    {
        $this->offlineEndTime['value'] = $offlineEndTime;
    }

    /**
     * Unsets Offline End Time.
     * Indicates the end of the time range for which to retrieve offline payments, in RFC 3339
     * format for timestamps. The range is determined using the
     * `offline_payment_details.client_created_at` field for each Payment. If set, payments without a
     * value set in `offline_payment_details.client_created_at` will not be returned.
     *
     * Default: The current time.
     */
    public function unsetOfflineEndTime(): void
    {
        $this->offlineEndTime = [];
    }

    /**
     * Returns Updated at Begin Time.
     * Indicates the start of the time range to retrieve payments for, in RFC 3339 format.  The
     * range is determined using the `updated_at` field for each Payment.
     */
    public function getUpdatedAtBeginTime(): ?string
    {
        if (count($this->updatedAtBeginTime) == 0) {
            return null;
        }
        return $this->updatedAtBeginTime['value'];
    }

    /**
     * Sets Updated at Begin Time.
     * Indicates the start of the time range to retrieve payments for, in RFC 3339 format.  The
     * range is determined using the `updated_at` field for each Payment.
     *
     * @maps updated_at_begin_time
     */
    public function setUpdatedAtBeginTime(?string $updatedAtBeginTime): void
    {
        $this->updatedAtBeginTime['value'] = $updatedAtBeginTime;
    }

    /**
     * Unsets Updated at Begin Time.
     * Indicates the start of the time range to retrieve payments for, in RFC 3339 format.  The
     * range is determined using the `updated_at` field for each Payment.
     */
    public function unsetUpdatedAtBeginTime(): void
    {
        $this->updatedAtBeginTime = [];
    }

    /**
     * Returns Updated at End Time.
     * Indicates the end of the time range to retrieve payments for, in RFC 3339 format.  The
     * range is determined using the `updated_at` field for each Payment.
     */
    public function getUpdatedAtEndTime(): ?string
    {
        if (count($this->updatedAtEndTime) == 0) {
            return null;
        }
        return $this->updatedAtEndTime['value'];
    }

    /**
     * Sets Updated at End Time.
     * Indicates the end of the time range to retrieve payments for, in RFC 3339 format.  The
     * range is determined using the `updated_at` field for each Payment.
     *
     * @maps updated_at_end_time
     */
    public function setUpdatedAtEndTime(?string $updatedAtEndTime): void
    {
        $this->updatedAtEndTime['value'] = $updatedAtEndTime;
    }

    /**
     * Unsets Updated at End Time.
     * Indicates the end of the time range to retrieve payments for, in RFC 3339 format.  The
     * range is determined using the `updated_at` field for each Payment.
     */
    public function unsetUpdatedAtEndTime(): void
    {
        $this->updatedAtEndTime = [];
    }

    /**
     * Returns Sort Field.
     */
    public function getSortField(): ?string
    {
        return $this->sortField;
    }

    /**
     * Sets Sort Field.
     *
     * @maps sort_field
     */
    public function setSortField(?string $sortField): void
    {
        $this->sortField = $sortField;
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
        if (!empty($this->beginTime)) {
            $json['begin_time']            = $this->beginTime['value'];
        }
        if (!empty($this->endTime)) {
            $json['end_time']              = $this->endTime['value'];
        }
        if (!empty($this->sortOrder)) {
            $json['sort_order']            = $this->sortOrder['value'];
        }
        if (!empty($this->cursor)) {
            $json['cursor']                = $this->cursor['value'];
        }
        if (!empty($this->locationId)) {
            $json['location_id']           = $this->locationId['value'];
        }
        if (!empty($this->total)) {
            $json['total']                 = $this->total['value'];
        }
        if (!empty($this->last4)) {
            $json['last_4']                = $this->last4['value'];
        }
        if (!empty($this->cardBrand)) {
            $json['card_brand']            = $this->cardBrand['value'];
        }
        if (!empty($this->limit)) {
            $json['limit']                 = $this->limit['value'];
        }
        if (!empty($this->isOfflinePayment)) {
            $json['is_offline_payment']    = $this->isOfflinePayment['value'];
        }
        if (!empty($this->offlineBeginTime)) {
            $json['offline_begin_time']    = $this->offlineBeginTime['value'];
        }
        if (!empty($this->offlineEndTime)) {
            $json['offline_end_time']      = $this->offlineEndTime['value'];
        }
        if (!empty($this->updatedAtBeginTime)) {
            $json['updated_at_begin_time'] = $this->updatedAtBeginTime['value'];
        }
        if (!empty($this->updatedAtEndTime)) {
            $json['updated_at_end_time']   = $this->updatedAtEndTime['value'];
        }
        if (isset($this->sortField)) {
            $json['sort_field']            = $this->sortField;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
