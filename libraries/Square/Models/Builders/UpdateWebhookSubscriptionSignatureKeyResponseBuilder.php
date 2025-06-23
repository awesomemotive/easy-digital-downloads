<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\UpdateWebhookSubscriptionSignatureKeyResponse;

/**
 * Builder for model UpdateWebhookSubscriptionSignatureKeyResponse
 *
 * @see UpdateWebhookSubscriptionSignatureKeyResponse
 */
class UpdateWebhookSubscriptionSignatureKeyResponseBuilder
{
    /**
     * @var UpdateWebhookSubscriptionSignatureKeyResponse
     */
    private $instance;

    private function __construct(UpdateWebhookSubscriptionSignatureKeyResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Webhook Subscription Signature Key Response Builder object.
     */
    public static function init(): self
    {
        return new self(new UpdateWebhookSubscriptionSignatureKeyResponse());
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
     * Sets signature key field.
     *
     * @param string|null $value
     */
    public function signatureKey(?string $value): self
    {
        $this->instance->setSignatureKey($value);
        return $this;
    }

    /**
     * Initializes a new Update Webhook Subscription Signature Key Response object.
     */
    public function build(): UpdateWebhookSubscriptionSignatureKeyResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
