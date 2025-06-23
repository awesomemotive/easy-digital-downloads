<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BusinessAppointmentSettings;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model BusinessAppointmentSettings
 *
 * @see BusinessAppointmentSettings
 */
class BusinessAppointmentSettingsBuilder
{
    /**
     * @var BusinessAppointmentSettings
     */
    private $instance;

    private function __construct(BusinessAppointmentSettings $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Business Appointment Settings Builder object.
     */
    public static function init(): self
    {
        return new self(new BusinessAppointmentSettings());
    }

    /**
     * Sets location types field.
     *
     * @param string[]|null $value
     */
    public function locationTypes(?array $value): self
    {
        $this->instance->setLocationTypes($value);
        return $this;
    }

    /**
     * Unsets location types field.
     */
    public function unsetLocationTypes(): self
    {
        $this->instance->unsetLocationTypes();
        return $this;
    }

    /**
     * Sets alignment time field.
     *
     * @param string|null $value
     */
    public function alignmentTime(?string $value): self
    {
        $this->instance->setAlignmentTime($value);
        return $this;
    }

    /**
     * Sets min booking lead time seconds field.
     *
     * @param int|null $value
     */
    public function minBookingLeadTimeSeconds(?int $value): self
    {
        $this->instance->setMinBookingLeadTimeSeconds($value);
        return $this;
    }

    /**
     * Unsets min booking lead time seconds field.
     */
    public function unsetMinBookingLeadTimeSeconds(): self
    {
        $this->instance->unsetMinBookingLeadTimeSeconds();
        return $this;
    }

    /**
     * Sets max booking lead time seconds field.
     *
     * @param int|null $value
     */
    public function maxBookingLeadTimeSeconds(?int $value): self
    {
        $this->instance->setMaxBookingLeadTimeSeconds($value);
        return $this;
    }

    /**
     * Unsets max booking lead time seconds field.
     */
    public function unsetMaxBookingLeadTimeSeconds(): self
    {
        $this->instance->unsetMaxBookingLeadTimeSeconds();
        return $this;
    }

    /**
     * Sets any team member booking enabled field.
     *
     * @param bool|null $value
     */
    public function anyTeamMemberBookingEnabled(?bool $value): self
    {
        $this->instance->setAnyTeamMemberBookingEnabled($value);
        return $this;
    }

    /**
     * Unsets any team member booking enabled field.
     */
    public function unsetAnyTeamMemberBookingEnabled(): self
    {
        $this->instance->unsetAnyTeamMemberBookingEnabled();
        return $this;
    }

    /**
     * Sets multiple service booking enabled field.
     *
     * @param bool|null $value
     */
    public function multipleServiceBookingEnabled(?bool $value): self
    {
        $this->instance->setMultipleServiceBookingEnabled($value);
        return $this;
    }

    /**
     * Unsets multiple service booking enabled field.
     */
    public function unsetMultipleServiceBookingEnabled(): self
    {
        $this->instance->unsetMultipleServiceBookingEnabled();
        return $this;
    }

    /**
     * Sets max appointments per day limit type field.
     *
     * @param string|null $value
     */
    public function maxAppointmentsPerDayLimitType(?string $value): self
    {
        $this->instance->setMaxAppointmentsPerDayLimitType($value);
        return $this;
    }

    /**
     * Sets max appointments per day limit field.
     *
     * @param int|null $value
     */
    public function maxAppointmentsPerDayLimit(?int $value): self
    {
        $this->instance->setMaxAppointmentsPerDayLimit($value);
        return $this;
    }

    /**
     * Unsets max appointments per day limit field.
     */
    public function unsetMaxAppointmentsPerDayLimit(): self
    {
        $this->instance->unsetMaxAppointmentsPerDayLimit();
        return $this;
    }

    /**
     * Sets cancellation window seconds field.
     *
     * @param int|null $value
     */
    public function cancellationWindowSeconds(?int $value): self
    {
        $this->instance->setCancellationWindowSeconds($value);
        return $this;
    }

    /**
     * Unsets cancellation window seconds field.
     */
    public function unsetCancellationWindowSeconds(): self
    {
        $this->instance->unsetCancellationWindowSeconds();
        return $this;
    }

    /**
     * Sets cancellation fee money field.
     *
     * @param Money|null $value
     */
    public function cancellationFeeMoney(?Money $value): self
    {
        $this->instance->setCancellationFeeMoney($value);
        return $this;
    }

    /**
     * Sets cancellation policy field.
     *
     * @param string|null $value
     */
    public function cancellationPolicy(?string $value): self
    {
        $this->instance->setCancellationPolicy($value);
        return $this;
    }

    /**
     * Sets cancellation policy text field.
     *
     * @param string|null $value
     */
    public function cancellationPolicyText(?string $value): self
    {
        $this->instance->setCancellationPolicyText($value);
        return $this;
    }

    /**
     * Unsets cancellation policy text field.
     */
    public function unsetCancellationPolicyText(): self
    {
        $this->instance->unsetCancellationPolicyText();
        return $this;
    }

    /**
     * Sets skip booking flow staff selection field.
     *
     * @param bool|null $value
     */
    public function skipBookingFlowStaffSelection(?bool $value): self
    {
        $this->instance->setSkipBookingFlowStaffSelection($value);
        return $this;
    }

    /**
     * Unsets skip booking flow staff selection field.
     */
    public function unsetSkipBookingFlowStaffSelection(): self
    {
        $this->instance->unsetSkipBookingFlowStaffSelection();
        return $this;
    }

    /**
     * Initializes a new Business Appointment Settings object.
     */
    public function build(): BusinessAppointmentSettings
    {
        return CoreHelper::clone($this->instance);
    }
}
