<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListMerchantsRequest;

/**
 * Builder for model ListMerchantsRequest
 *
 * @see ListMerchantsRequest
 */
class ListMerchantsRequestBuilder
{
    /**
     * @var ListMerchantsRequest
     */
    private $instance;

    private function __construct(ListMerchantsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Merchants Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListMerchantsRequest());
    }

    /**
     * Sets cursor field.
     *
     * @param int|null $value
     */
    public function cursor(?int $value): self
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
     * Initializes a new List Merchants Request object.
     */
    public function build(): ListMerchantsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
