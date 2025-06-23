<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateWebhookSubscriptionRequest;
use EDD\Vendor\Square\Models\WebhookSubscription;

/**
 * Builder for model CreateWebhookSubscriptionRequest
 *
 * @see CreateWebhookSubscriptionRequest
 */
class CreateWebhookSubscriptionRequestBuilder
{
    /**
     * @var CreateWebhookSubscriptionRequest
     */
    private $instance;

    private function __construct(CreateWebhookSubscriptionRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Webhook Subscription Request Builder object.
     *
     * @param WebhookSubscription $subscription
     */
    public static function init(WebhookSubscription $subscription): self
    {
        return new self(new CreateWebhookSubscriptionRequest($subscription));
    }

    /**
     * Sets idempotency key field.
     *
     * @param string|null $value
     */
    public function idempotencyKey(?string $value): self
    {
        $this->instance->setIdempotencyKey($value);
        return $this;
    }

    /**
     * Initializes a new Create Webhook Subscription Request object.
     */
    public function build(): CreateWebhookSubscriptionRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
