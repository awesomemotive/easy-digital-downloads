<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\RetrieveSubscriptionRequest;

/**
 * Builder for model RetrieveSubscriptionRequest
 *
 * @see RetrieveSubscriptionRequest
 */
class RetrieveSubscriptionRequestBuilder
{
    /**
     * @var RetrieveSubscriptionRequest
     */
    private $instance;

    private function __construct(RetrieveSubscriptionRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Subscription Request Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveSubscriptionRequest());
    }

    /**
     * Sets include field.
     *
     * @param string|null $value
     */
    public function include(?string $value): self
    {
        $this->instance->setInclude($value);
        return $this;
    }

    /**
     * Unsets include field.
     */
    public function unsetInclude(): self
    {
        $this->instance->unsetInclude();
        return $this;
    }

    /**
     * Initializes a new Retrieve Subscription Request object.
     */
    public function build(): RetrieveSubscriptionRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
