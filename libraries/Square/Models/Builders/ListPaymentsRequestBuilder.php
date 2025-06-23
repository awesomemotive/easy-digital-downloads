<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListPaymentsRequest;

/**
 * Builder for model ListPaymentsRequest
 *
 * @see ListPaymentsRequest
 */
class ListPaymentsRequestBuilder
{
    /**
     * @var ListPaymentsRequest
     */
    private $instance;

    private function __construct(ListPaymentsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Payments Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListPaymentsRequest());
    }

    /**
     * Sets begin time field.
     *
     * @param string|null $value
     */
    public function beginTime(?string $value): self
    {
        $this->instance->setBeginTime($value);
        return $this;
    }

    /**
     * Unsets begin time field.
     */
    public function unsetBeginTime(): self
    {
        $this->instance->unsetBeginTime();
        return $this;
    }

    /**
     * Sets end time field.
     *
     * @param string|null $value
     */
    public function endTime(?string $value): self
    {
        $this->instance->setEndTime($value);
        return $this;
    }

    /**
     * Unsets end time field.
     */
    public function unsetEndTime(): self
    {
        $this->instance->unsetEndTime();
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
     * Unsets sort order field.
     */
    public function unsetSortOrder(): self
    {
        $this->instance->unsetSortOrder();
        return $this;
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
     * Unsets location id field.
     */
    public function unsetLocationId(): self
    {
        $this->instance->unsetLocationId();
        return $this;
    }

    /**
     * Sets total field.
     *
     * @param int|null $value
     */
    public function total(?int $value): self
    {
        $this->instance->setTotal($value);
        return $this;
    }

    /**
     * Unsets total field.
     */
    public function unsetTotal(): self
    {
        $this->instance->unsetTotal();
        return $this;
    }

    /**
     * Sets last 4 field.
     *
     * @param string|null $value
     */
    public function last4(?string $value): self
    {
        $this->instance->setLast4($value);
        return $this;
    }

    /**
     * Unsets last 4 field.
     */
    public function unsetLast4(): self
    {
        $this->instance->unsetLast4();
        return $this;
    }

    /**
     * Sets card brand field.
     *
     * @param string|null $value
     */
    public function cardBrand(?string $value): self
    {
        $this->instance->setCardBrand($value);
        return $this;
    }

    /**
     * Unsets card brand field.
     */
    public function unsetCardBrand(): self
    {
        $this->instance->unsetCardBrand();
        return $this;
    }

    /**
     * Sets limit field.
     *
     * @param int|null $value
     */
    public function limit(?int $value): self
    {
        $this->instance->setLimit($value);
        return $this;
    }

    /**
     * Unsets limit field.
     */
    public function unsetLimit(): self
    {
        $this->instance->unsetLimit();
        return $this;
    }

    /**
     * Sets is offline payment field.
     *
     * @param bool|null $value
     */
    public function isOfflinePayment(?bool $value): self
    {
        $this->instance->setIsOfflinePayment($value);
        return $this;
    }

    /**
     * Unsets is offline payment field.
     */
    public function unsetIsOfflinePayment(): self
    {
        $this->instance->unsetIsOfflinePayment();
        return $this;
    }

    /**
     * Sets offline begin time field.
     *
     * @param string|null $value
     */
    public function offlineBeginTime(?string $value): self
    {
        $this->instance->setOfflineBeginTime($value);
        return $this;
    }

    /**
     * Unsets offline begin time field.
     */
    public function unsetOfflineBeginTime(): self
    {
        $this->instance->unsetOfflineBeginTime();
        return $this;
    }

    /**
     * Sets offline end time field.
     *
     * @param string|null $value
     */
    public function offlineEndTime(?string $value): self
    {
        $this->instance->setOfflineEndTime($value);
        return $this;
    }

    /**
     * Unsets offline end time field.
     */
    public function unsetOfflineEndTime(): self
    {
        $this->instance->unsetOfflineEndTime();
        return $this;
    }

    /**
     * Sets updated at begin time field.
     *
     * @param string|null $value
     */
    public function updatedAtBeginTime(?string $value): self
    {
        $this->instance->setUpdatedAtBeginTime($value);
        return $this;
    }

    /**
     * Unsets updated at begin time field.
     */
    public function unsetUpdatedAtBeginTime(): self
    {
        $this->instance->unsetUpdatedAtBeginTime();
        return $this;
    }

    /**
     * Sets updated at end time field.
     *
     * @param string|null $value
     */
    public function updatedAtEndTime(?string $value): self
    {
        $this->instance->setUpdatedAtEndTime($value);
        return $this;
    }

    /**
     * Unsets updated at end time field.
     */
    public function unsetUpdatedAtEndTime(): self
    {
        $this->instance->unsetUpdatedAtEndTime();
        return $this;
    }

    /**
     * Sets sort field field.
     *
     * @param string|null $value
     */
    public function sortField(?string $value): self
    {
        $this->instance->setSortField($value);
        return $this;
    }

    /**
     * Initializes a new List Payments Request object.
     */
    public function build(): ListPaymentsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
