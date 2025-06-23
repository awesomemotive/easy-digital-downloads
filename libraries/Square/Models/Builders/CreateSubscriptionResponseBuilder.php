<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateSubscriptionResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Subscription;

/**
 * Builder for model CreateSubscriptionResponse
 *
 * @see CreateSubscriptionResponse
 */
class CreateSubscriptionResponseBuilder
{
    /**
     * @var CreateSubscriptionResponse
     */
    private $instance;

    private function __construct(CreateSubscriptionResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Subscription Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateSubscriptionResponse());
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
     * Initializes a new Create Subscription Response object.
     */
    public function build(): CreateSubscriptionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
