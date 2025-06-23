<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\WebhookSubscription;

/**
 * Builder for model WebhookSubscription
 *
 * @see WebhookSubscription
 */
class WebhookSubscriptionBuilder
{
    /**
     * @var WebhookSubscription
     */
    private $instance;

    private function __construct(WebhookSubscription $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Webhook Subscription Builder object.
     */
    public static function init(): self
    {
        return new self(new WebhookSubscription());
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets name field.
     *
     * @param string|null $value
     */
    public function name(?string $value): self
    {
        $this->instance->setName($value);
        return $this;
    }

    /**
     * Unsets name field.
     */
    public function unsetName(): self
    {
        $this->instance->unsetName();
        return $this;
    }

    /**
     * Sets enabled field.
     *
     * @param bool|null $value
     */
    public function enabled(?bool $value): self
    {
        $this->instance->setEnabled($value);
        return $this;
    }

    /**
     * Unsets enabled field.
     */
    public function unsetEnabled(): self
    {
        $this->instance->unsetEnabled();
        return $this;
    }

    /**
     * Sets event types field.
     *
     * @param string[]|null $value
     */
    public function eventTypes(?array $value): self
    {
        $this->instance->setEventTypes($value);
        return $this;
    }

    /**
     * Unsets event types field.
     */
    public function unsetEventTypes(): self
    {
        $this->instance->unsetEventTypes();
        return $this;
    }

    /**
     * Sets notification url field.
     *
     * @param string|null $value
     */
    public function notificationUrl(?string $value): self
    {
        $this->instance->setNotificationUrl($value);
        return $this;
    }

    /**
     * Unsets notification url field.
     */
    public function unsetNotificationUrl(): self
    {
        $this->instance->unsetNotificationUrl();
        return $this;
    }

    /**
     * Sets api version field.
     *
     * @param string|null $value
     */
    public function apiVersion(?string $value): self
    {
        $this->instance->setApiVersion($value);
        return $this;
    }

    /**
     * Unsets api version field.
     */
    public function unsetApiVersion(): self
    {
        $this->instance->unsetApiVersion();
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
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Initializes a new Webhook Subscription object.
     */
    public function build(): WebhookSubscription
    {
        return CoreHelper::clone($this->instance);
    }
}
