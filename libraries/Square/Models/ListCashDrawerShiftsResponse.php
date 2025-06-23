<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class ListCashDrawerShiftsResponse implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $cursor;

    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * @var CashDrawerShiftSummary[]|null
     */
    private $cashDrawerShifts;

    /**
     * Returns Cursor.
     * Opaque cursor for fetching the next page of results. Cursor is not
     * present in the last page of results.
     */
    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    /**
     * Sets Cursor.
     * Opaque cursor for fetching the next page of results. Cursor is not
     * present in the last page of results.
     *
     * @maps cursor
     */
    public function setCursor(?string $cursor): void
    {
        $this->cursor = $cursor;
    }

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
     * Returns Cash Drawer Shifts.
     * A collection of CashDrawerShiftSummary objects for shifts that match
     * the query.
     *
     * @return CashDrawerShiftSummary[]|null
     */
    public function getCashDrawerShifts(): ?array
    {
        return $this->cashDrawerShifts;
    }

    /**
     * Sets Cash Drawer Shifts.
     * A collection of CashDrawerShiftSummary objects for shifts that match
     * the query.
     *
     * @maps cash_drawer_shifts
     *
     * @param CashDrawerShiftSummary[]|null $cashDrawerShifts
     */
    public function setCashDrawerShifts(?array $cashDrawerShifts): void
    {
        $this->cashDrawerShifts = $cashDrawerShifts;
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
        if (isset($this->cursor)) {
            $json['cursor']             = $this->cursor;
        }
        if (isset($this->errors)) {
            $json['errors']             = $this->errors;
        }
        if (isset($this->cashDrawerShifts)) {
            $json['cash_drawer_shifts'] = $this->cashDrawerShifts;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
