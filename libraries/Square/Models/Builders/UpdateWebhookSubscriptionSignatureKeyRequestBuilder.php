<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\UpdateWebhookSubscriptionSignatureKeyRequest;

/**
 * Builder for model UpdateWebhookSubscriptionSignatureKeyRequest
 *
 * @see UpdateWebhookSubscriptionSignatureKeyRequest
 */
class UpdateWebhookSubscriptionSignatureKeyRequestBuilder
{
    /**
     * @var UpdateWebhookSubscriptionSignatureKeyRequest
     */
    private $instance;

    private function __construct(UpdateWebhookSubscriptionSignatureKeyRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Webhook Subscription Signature Key Request Builder object.
     */
    public static function init(): self
    {
        return new self(new UpdateWebhookSubscriptionSignatureKeyRequest());
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
     * Unsets idempotency key field.
     */
    public function unsetIdempotencyKey(): self
    {
        $this->instance->unsetIdempotencyKey();
        return $this;
    }

    /**
     * Initializes a new Update Webhook Subscription Signature Key Request object.
     */
    public function build(): UpdateWebhookSubscriptionSignatureKeyRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
