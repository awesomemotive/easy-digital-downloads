<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\EventTypeMetadata;
use EDD\Vendor\Square\Models\ListWebhookEventTypesResponse;

/**
 * Builder for model ListWebhookEventTypesResponse
 *
 * @see ListWebhookEventTypesResponse
 */
class ListWebhookEventTypesResponseBuilder
{
    /**
     * @var ListWebhookEventTypesResponse
     */
    private $instance;

    private function __construct(ListWebhookEventTypesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Webhook Event Types Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListWebhookEventTypesResponse());
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
     * Sets metadata field.
     *
     * @param EventTypeMetadata[]|null $value
     */
    public function metadata(?array $value): self
    {
        $this->instance->setMetadata($value);
        return $this;
    }

    /**
     * Initializes a new List Webhook Event Types Response object.
     */
    public function build(): ListWebhookEventTypesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
