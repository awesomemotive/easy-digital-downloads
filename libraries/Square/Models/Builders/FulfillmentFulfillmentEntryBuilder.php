<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\FulfillmentFulfillmentEntry;

/**
 * Builder for model FulfillmentFulfillmentEntry
 *
 * @see FulfillmentFulfillmentEntry
 */
class FulfillmentFulfillmentEntryBuilder
{
    /**
     * @var FulfillmentFulfillmentEntry
     */
    private $instance;

    private function __construct(FulfillmentFulfillmentEntry $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Fulfillment Fulfillment Entry Builder object.
     *
     * @param string $lineItemUid
     * @param string $quantity
     */
    public static function init(string $lineItemUid, string $quantity): self
    {
        return new self(new FulfillmentFulfillmentEntry($lineItemUid, $quantity));
    }

    /**
     * Sets uid field.
     *
     * @param string|null $value
     */
    public function uid(?string $value): self
    {
        $this->instance->setUid($value);
        return $this;
    }

    /**
     * Unsets uid field.
     */
    public function unsetUid(): self
    {
        $this->instance->unsetUid();
        return $this;
    }

    /**
     * Sets metadata field.
     *
     * @param array<string,string>|null $value
     */
    public function metadata(?array $value): self
    {
        $this->instance->setMetadata($value);
        return $this;
    }

    /**
     * Unsets metadata field.
     */
    public function unsetMetadata(): self
    {
        $this->instance->unsetMetadata();
        return $this;
    }

    /**
     * Initializes a new Fulfillment Fulfillment Entry object.
     */
    public function build(): FulfillmentFulfillmentEntry
    {
        return CoreHelper::clone($this->instance);
    }
}
