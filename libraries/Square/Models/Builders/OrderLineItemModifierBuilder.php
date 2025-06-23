<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\OrderLineItemModifier;

/**
 * Builder for model OrderLineItemModifier
 *
 * @see OrderLineItemModifier
 */
class OrderLineItemModifierBuilder
{
    /**
     * @var OrderLineItemModifier
     */
    private $instance;

    private function __construct(OrderLineItemModifier $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Line Item Modifier Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderLineItemModifier());
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
     * Sets catalog object id field.
     *
     * @param string|null $value
     */
    public function catalogObjectId(?string $value): self
    {
        $this->instance->setCatalogObjectId($value);
        return $this;
    }

    /**
     * Unsets catalog object id field.
     */
    public function unsetCatalogObjectId(): self
    {
        $this->instance->unsetCatalogObjectId();
        return $this;
    }

    /**
     * Sets catalog version field.
     *
     * @param int|null $value
     */
    public function catalogVersion(?int $value): self
    {
        $this->instance->setCatalogVersion($value);
        return $this;
    }

    /**
     * Unsets catalog version field.
     */
    public function unsetCatalogVersion(): self
    {
        $this->instance->unsetCatalogVersion();
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
     * Sets quantity field.
     *
     * @param string|null $value
     */
    public function quantity(?string $value): self
    {
        $this->instance->setQuantity($value);
        return $this;
    }

    /**
     * Unsets quantity field.
     */
    public function unsetQuantity(): self
    {
        $this->instance->unsetQuantity();
        return $this;
    }

    /**
     * Sets base price money field.
     *
     * @param Money|null $value
     */
    public function basePriceMoney(?Money $value): self
    {
        $this->instance->setBasePriceMoney($value);
        return $this;
    }

    /**
     * Sets total price money field.
     *
     * @param Money|null $value
     */
    public function totalPriceMoney(?Money $value): self
    {
        $this->instance->setTotalPriceMoney($value);
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
     * Initializes a new Order Line Item Modifier object.
     */
    public function build(): OrderLineItemModifier
    {
        return CoreHelper::clone($this->instance);
    }
}
