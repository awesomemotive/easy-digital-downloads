<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CashDrawerDevice;
use EDD\Vendor\Square\Models\CashDrawerShift;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model CashDrawerShift
 *
 * @see CashDrawerShift
 */
class CashDrawerShiftBuilder
{
    /**
     * @var CashDrawerShift
     */
    private $instance;

    private function __construct(CashDrawerShift $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Cash Drawer Shift Builder object.
     */
    public static function init(): self
    {
        return new self(new CashDrawerShift());
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
     * Sets cash payment money field.
     *
     * @param Money|null $value
     */
    public function cashPaymentMoney(?Money $value): self
    {
        $this->instance->setCashPaymentMoney($value);
        return $this;
    }

    /**
     * Sets cash refunds money field.
     *
     * @param Money|null $value
     */
    public function cashRefundsMoney(?Money $value): self
    {
        $this->instance->setCashRefundsMoney($value);
        return $this;
    }

    /**
     * Sets cash paid in money field.
     *
     * @param Money|null $value
     */
    public function cashPaidInMoney(?Money $value): self
    {
        $this->instance->setCashPaidInMoney($value);
        return $this;
    }

    /**
     * Sets cash paid out money field.
     *
     * @param Money|null $value
     */
    public function cashPaidOutMoney(?Money $value): self
    {
        $this->instance->setCashPaidOutMoney($value);
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
     * Sets device field.
     *
     * @param CashDrawerDevice|null $value
     */
    public function device(?CashDrawerDevice $value): self
    {
        $this->instance->setDevice($value);
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
     * Sets team member ids field.
     *
     * @param string[]|null $value
     */
    public function teamMemberIds(?array $value): self
    {
        $this->instance->setTeamMemberIds($value);
        return $this;
    }

    /**
     * Sets opening team member id field.
     *
     * @param string|null $value
     */
    public function openingTeamMemberId(?string $value): self
    {
        $this->instance->setOpeningTeamMemberId($value);
        return $this;
    }

    /**
     * Sets ending team member id field.
     *
     * @param string|null $value
     */
    public function endingTeamMemberId(?string $value): self
    {
        $this->instance->setEndingTeamMemberId($value);
        return $this;
    }

    /**
     * Sets closing team member id field.
     *
     * @param string|null $value
     */
    public function closingTeamMemberId(?string $value): self
    {
        $this->instance->setClosingTeamMemberId($value);
        return $this;
    }

    /**
     * Initializes a new Cash Drawer Shift object.
     */
    public function build(): CashDrawerShift
    {
        return CoreHelper::clone($this->instance);
    }
}
