<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Defines output parameters in a response of the
 * [BulkSwapPlan]($e/Subscriptions/BulkSwapPlan) endpoint.
 */
class BulkSwapPlanResponse implements \JsonSerializable
{
    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * @var int|null
     */
    private $affectedSubscriptions;

    /**
     * Returns Errors.
     * Errors encountered during the request.
     *
     * @return Error[]|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Sets Errors.
     * Errors encountered during the request.
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
     * Returns Affected Subscriptions.
     * The number of affected subscriptions.
     */
    public function getAffectedSubscriptions(): ?int
    {
        return $this->affectedSubscriptions;
    }

    /**
     * Sets Affected Subscriptions.
     * The number of affected subscriptions.
     *
     * @maps affected_subscriptions
     */
    public function setAffectedSubscriptions(?int $affectedSubscriptions): void
    {
        $this->affectedSubscriptions = $affectedSubscriptions;
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
            $json['errors']                 = $this->errors;
        }
        if (isset($this->affectedSubscriptions)) {
            $json['affected_subscriptions'] = $this->affectedSubscriptions;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
