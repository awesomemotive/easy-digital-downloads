<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListWebhookSubscriptionsRequest;

/**
 * Builder for model ListWebhookSubscriptionsRequest
 *
 * @see ListWebhookSubscriptionsRequest
 */
class ListWebhookSubscriptionsRequestBuilder
{
    /**
     * @var ListWebhookSubscriptionsRequest
     */
    private $instance;

    private function __construct(ListWebhookSubscriptionsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Webhook Subscriptions Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListWebhookSubscriptionsRequest());
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
        return $this;
    }

    /**
     * Unsets cursor field.
     */
    public function unsetCursor(): self
    {
        $this->instance->unsetCursor();
        return $this;
    }

    /**
     * Sets include disabled field.
     *
     * @param bool|null $value
     */
    public function includeDisabled(?bool $value): self
    {
        $this->instance->setIncludeDisabled($value);
        return $this;
    }

    /**
     * Unsets include disabled field.
     */
    public function unsetIncludeDisabled(): self
    {
        $this->instance->unsetIncludeDisabled();
        return $this;
    }

    /**
     * Sets sort order field.
     *
     * @param string|null $value
     */
    public function sortOrder(?string $value): self
    {
        $this->instance->setSortOrder($value);
        return $this;
    }

    /**
     * Sets limit field.
     *
     * @param int|null $value
     */
    public function limit(?int $value): self
    {
        $this->instance->setLimit($value);
        return $this;
    }

    /**
     * Unsets limit field.
     */
    public function unsetLimit(): self
    {
        $this->instance->unsetLimit();
        return $this;
    }

    /**
     * Initializes a new List Webhook Subscriptions Request object.
     */
    public function build(): ListWebhookSubscriptionsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
