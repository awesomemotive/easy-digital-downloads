<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\GiftCard;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model GiftCard
 *
 * @see GiftCard
 */
class GiftCardBuilder
{
    /**
     * @var GiftCard
     */
    private $instance;

    private function __construct(GiftCard $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Gift Card Builder object.
     *
     * @param string $type
     */
    public static function init(string $type): self
    {
        return new self(new GiftCard($type));
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
     * Sets gan source field.
     *
     * @param string|null $value
     */
    public function ganSource(?string $value): self
    {
        $this->instance->setGanSource($value);
        return $this;
    }

    /**
     * Sets state field.
     *
     * @param string|null $value
     */
    public function state(?string $value): self
    {
        $this->instance->setState($value);
        return $this;
    }

    /**
     * Sets balance money field.
     *
     * @param Money|null $value
     */
    public function balanceMoney(?Money $value): self
    {
        $this->instance->setBalanceMoney($value);
        return $this;
    }

    /**
     * Sets gan field.
     *
     * @param string|null $value
     */
    public function gan(?string $value): self
    {
        $this->instance->setGan($value);
        return $this;
    }

    /**
     * Unsets gan field.
     */
    public function unsetGan(): self
    {
        $this->instance->unsetGan();
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
     * Sets customer ids field.
     *
     * @param string[]|null $value
     */
    public function customerIds(?array $value): self
    {
        $this->instance->setCustomerIds($value);
        return $this;
    }

    /**
     * Initializes a new Gift Card object.
     */
    public function build(): GiftCard
    {
        return CoreHelper::clone($this->instance);
    }
}
