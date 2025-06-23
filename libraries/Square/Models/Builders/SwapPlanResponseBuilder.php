<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Subscription;
use EDD\Vendor\Square\Models\SubscriptionAction;
use EDD\Vendor\Square\Models\SwapPlanResponse;

/**
 * Builder for model SwapPlanResponse
 *
 * @see SwapPlanResponse
 */
class SwapPlanResponseBuilder
{
    /**
     * @var SwapPlanResponse
     */
    private $instance;

    private function __construct(SwapPlanResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Swap Plan Response Builder object.
     */
    public static function init(): self
    {
        return new self(new SwapPlanResponse());
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
     * Initializes a new Swap Plan Response object.
     */
    public function build(): SwapPlanResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
