<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Additional details about EDD\Vendor\Square Account payments.
 */
class SquareAccountDetails implements \JsonSerializable
{
    /**
     * @var array
     */
    private $paymentSourceToken = [];

    /**
     * @var array
     */
    private $errors = [];

    /**
     * Returns Payment Source Token.
     * Unique identifier for the payment source used for this payment.
     */
    public function getPaymentSourceToken(): ?string
    {
        if (count($this->paymentSourceToken) == 0) {
            return null;
        }
        return $this->paymentSourceToken['value'];
    }

    /**
     * Sets Payment Source Token.
     * Unique identifier for the payment source used for this payment.
     *
     * @maps payment_source_token
     */
    public function setPaymentSourceToken(?string $paymentSourceToken): void
    {
        $this->paymentSourceToken['value'] = $paymentSourceToken;
    }

    /**
     * Unsets Payment Source Token.
     * Unique identifier for the payment source used for this payment.
     */
    public function unsetPaymentSourceToken(): void
    {
        $this->paymentSourceToken = [];
    }

    /**
     * Returns Errors.
     * Information about errors encountered during the request.
     *
     * @return Error[]|null
     */
    public function getErrors(): ?array
    {
        if (count($this->errors) == 0) {
            return null;
        }
        return $this->errors['value'];
    }

    /**
     * Sets Errors.
     * Information about errors encountered during the request.
     *
     * @maps errors
     *
     * @param Error[]|null $errors
     */
    public function setErrors(?array $errors): void
    {
        $this->errors['value'] = $errors;
    }

    /**
     * Unsets Errors.
     * Information about errors encountered during the request.
     */
    public function unsetErrors(): void
    {
        $this->errors = [];
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
        if (!empty($this->paymentSourceToken)) {
            $json['payment_source_token'] = $this->paymentSourceToken['value'];
        }
        if (!empty($this->errors)) {
            $json['errors']               = $this->errors['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
