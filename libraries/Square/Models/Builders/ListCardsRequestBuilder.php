<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListCardsRequest;

/**
 * Builder for model ListCardsRequest
 *
 * @see ListCardsRequest
 */
class ListCardsRequestBuilder
{
    /**
     * @var ListCardsRequest
     */
    private $instance;

    private function __construct(ListCardsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Cards Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListCardsRequest());
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
     * Sets customer id field.
     *
     * @param string|null $value
     */
    public function customerId(?string $value): self
    {
        $this->instance->setCustomerId($value);
        return $this;
    }

    /**
     * Unsets customer id field.
     */
    public function unsetCustomerId(): self
    {
        $this->instance->unsetCustomerId();
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
     * Sets reference id field.
     *
     * @param string|null $value
     */
    public function referenceId(?string $value): self
    {
        $this->instance->setReferenceId($value);
        return $this;
    }

    /**
     * Unsets reference id field.
     */
    public function unsetReferenceId(): self
    {
        $this->instance->unsetReferenceId();
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
     * Initializes a new List Cards Request object.
     */
    public function build(): ListCardsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
