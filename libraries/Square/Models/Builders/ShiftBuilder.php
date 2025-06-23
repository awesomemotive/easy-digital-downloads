<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\MBreak;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\Shift;
use EDD\Vendor\Square\Models\ShiftWage;

/**
 * Builder for model Shift
 *
 * @see Shift
 */
class ShiftBuilder
{
    /**
     * @var Shift
     */
    private $instance;

    private function __construct(Shift $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Shift Builder object.
     *
     * @param string $locationId
     * @param string $startAt
     */
    public static function init(string $locationId, string $startAt): self
    {
        return new self(new Shift($locationId, $startAt));
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
     * Sets employee id field.
     *
     * @param string|null $value
     */
    public function employeeId(?string $value): self
    {
        $this->instance->setEmployeeId($value);
        return $this;
    }

    /**
     * Unsets employee id field.
     */
    public function unsetEmployeeId(): self
    {
        $this->instance->unsetEmployeeId();
        return $this;
    }

    /**
     * Sets timezone field.
     *
     * @param string|null $value
     */
    public function timezone(?string $value): self
    {
        $this->instance->setTimezone($value);
        return $this;
    }

    /**
     * Unsets timezone field.
     */
    public function unsetTimezone(): self
    {
        $this->instance->unsetTimezone();
        return $this;
    }

    /**
     * Sets end at field.
     *
     * @param string|null $value
     */
    public function endAt(?string $value): self
    {
        $this->instance->setEndAt($value);
        return $this;
    }

    /**
     * Unsets end at field.
     */
    public function unsetEndAt(): self
    {
        $this->instance->unsetEndAt();
        return $this;
    }

    /**
     * Sets wage field.
     *
     * @param ShiftWage|null $value
     */
    public function wage(?ShiftWage $value): self
    {
        $this->instance->setWage($value);
        return $this;
    }

    /**
     * Sets breaks field.
     *
     * @param MBreak[]|null $value
     */
    public function breaks(?array $value): self
    {
        $this->instance->setBreaks($value);
        return $this;
    }

    /**
     * Unsets breaks field.
     */
    public function unsetBreaks(): self
    {
        $this->instance->unsetBreaks();
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Sets version field.
     *
     * @param int|null $value
     */
    public function version(?int $value): self
    {
        $this->instance->setVersion($value);
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
     * Sets team member id field.
     *
     * @param string|null $value
     */
    public function teamMemberId(?string $value): self
    {
        $this->instance->setTeamMemberId($value);
        return $this;
    }

    /**
     * Unsets team member id field.
     */
    public function unsetTeamMemberId(): self
    {
        $this->instance->unsetTeamMemberId();
        return $this;
    }

    /**
     * Sets declared cash tip money field.
     *
     * @param Money|null $value
     */
    public function declaredCashTipMoney(?Money $value): self
    {
        $this->instance->setDeclaredCashTipMoney($value);
        return $this;
    }

    /**
     * Initializes a new Shift object.
     */
    public function build(): Shift
    {
        return CoreHelper::clone($this->instance);
    }
}
