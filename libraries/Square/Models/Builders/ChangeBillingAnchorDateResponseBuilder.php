<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ChangeBillingAnchorDateResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Subscription;
use EDD\Vendor\Square\Models\SubscriptionAction;

/**
 * Builder for model ChangeBillingAnchorDateResponse
 *
 * @see ChangeBillingAnchorDateResponse
 */
class ChangeBillingAnchorDateResponseBuilder
{
    /**
     * @var ChangeBillingAnchorDateResponse
     */
    private $instance;

    private function __construct(ChangeBillingAnchorDateResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Change Billing Anchor Date Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ChangeBillingAnchorDateResponse());
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Sets subscription field.
     *
     * @param Subscription|null $value
     */
    public function subscription(?Subscription $value): self
    {
        $this->instance->setSubscription($value);
        return $this;
    }

    /**
     * Sets actions field.
     *
     * @param SubscriptionAction[]|null $value
     */
    public function actions(?array $value): self
    {
        $this->instance->setActions($value);
        return $this;
    }

    /**
     * Initializes a new Change Billing Anchor Date Response object.
     */
    public function build(): ChangeBillingAnchorDateResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
