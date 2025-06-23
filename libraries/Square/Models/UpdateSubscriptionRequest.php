<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Defines input parameters in a request to the
 * [UpdateSubscription]($e/Subscriptions/UpdateSubscription) endpoint.
 */
class UpdateSubscriptionRequest implements \JsonSerializable
{
    /**
     * @var Subscription|null
     */
    private $subscription;

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
        if (isset($this->subscription)) {
            $json['subscription'] = $this->subscription;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
