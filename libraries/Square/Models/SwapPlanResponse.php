<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Defines output parameters in a response of the
 * [SwapPlan]($e/Subscriptions/SwapPlan) endpoint.
 */
class SwapPlanResponse implements \JsonSerializable
{
    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * @var Subscription|null
     */
    private $subscription;

    /**
     * @var SubscriptionAction[]|null
     */
    private $actions;

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
     * Returns Subscription.
     * Represents a subscription purchased by a customer.
     *
     * For more information, see
     * [Manage Subscriptions](https://developer.squareup.com/docs/subscriptions-api/manage-subscriptions).
     */
    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    /**
     * Sets Subscription.
     * Represents a subscription purchased by a customer.
     *
     * For more information, see
     * [Manage Subscriptions](https://developer.squareup.com/docs/subscriptions-api/manage-subscriptions).
     *
     * @maps subscription
     */
    public function setSubscription(?Subscription $subscription): void
    {
        $this->subscription = $subscription;
    }

    /**
     * Returns Actions.
     * A list of a `SWAP_PLAN` action created by the request.
     *
     * @return SubscriptionAction[]|null
     */
    public function getActions(): ?array
    {
        return $this->actions;
    }

    /**
     * Sets Actions.
     * A list of a `SWAP_PLAN` action created by the request.
     *
     * @maps actions
     *
     * @param SubscriptionAction[]|null $actions
     */
    public function setActions(?array $actions): void
    {
        $this->actions = $actions;
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
            $json['errors']       = $this->errors;
        }
        if (isset($this->subscription)) {
            $json['subscription'] = $this->subscription;
        }
        if (isset($this->actions)) {
            $json['actions']      = $this->actions;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
