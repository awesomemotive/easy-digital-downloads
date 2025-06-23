<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CashDrawerShiftSummary;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model CashDrawerShiftSummary
 *
 * @see CashDrawerShiftSummary
 */
class CashDrawerShiftSummaryBuilder
{
    /**
     * @var CashDrawerShiftSummary
     */
    private $instance;

    private function __construct(CashDrawerShiftSummary $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Cash Drawer Shift Summary Builder object.
     */
    public static function init(): self
    {
        return new self(new CashDrawerShiftSummary());
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
     * Sets opened at field.
     *
     * @param string|null $value
     */
    public function openedAt(?string $value): self
    {
        $this->instance->setOpenedAt($value);
        return $this;
    }

    /**
     * Unsets opened at field.
     */
    public function unsetOpenedAt(): self
    {
        $this->instance->unsetOpenedAt();
        return $this;
    }

    /**
     * Sets ended at field.
     *
     * @param string|null $value
     */
    public function endedAt(?string $value): self
    {
        $this->instance->setEndedAt($value);
        return $this;
    }

    /**
     * Unsets ended at field.
     */
    public function unsetEndedAt(): self
    {
        $this->instance->unsetEndedAt();
        return $this;
    }

    /**
     * Sets closed at field.
     *
     * @param string|null $value
     */
    public function closedAt(?string $value): self
    {
        $this->instance->setClosedAt($value);
        return $this;
    }

    /**
     * Unsets closed at field.
     */
    public function unsetClosedAt(): self
    {
        $this->instance->unsetClosedAt();
        return $this;
    }

    /**
     * Sets description field.
     *
     * @param string|null $value
     */
    public function description(?string $value): self
    {
        $this->instance->setDescription($value);
        return $this;
    }

    /**
     * Unsets description field.
     */
    public function unsetDescription(): self
    {
        $this->instance->unsetDescription();
        return $this;
    }

    /**
     * Sets opened cash money field.
     *
     * @param Money|null $value
     */
    public function openedCashMoney(?Money $value): self
    {
        $this->instance->setOpenedCashMoney($value);
        return $this;
    }

    /**
     * Sets expected cash money field.
     *
     * @param Money|null $value
     */
    public function expectedCashMoney(?Money $value): self
    {
        $this->instance->setExpectedCashMoney($value);
        return $this;
    }

    /**
     * Sets closed cash money field.
     *
     * @param Money|null $value
     */
    public function closedCashMoney(?Money $value): self
    {
        $this->instance->setClosedCashMoney($value);
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
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Initializes a new Cash Drawer Shift Summary object.
     */
    public function build(): CashDrawerShiftSummary
    {
        return CoreHelper::clone($this->instance);
    }
}
