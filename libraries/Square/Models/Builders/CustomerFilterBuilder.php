<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerCreationSourceFilter;
use EDD\Vendor\Square\Models\CustomerCustomAttributeFilters;
use EDD\Vendor\Square\Models\CustomerFilter;
use EDD\Vendor\Square\Models\CustomerTextFilter;
use EDD\Vendor\Square\Models\FilterValue;
use EDD\Vendor\Square\Models\TimeRange;

/**
 * Builder for model CustomerFilter
 *
 * @see CustomerFilter
 */
class CustomerFilterBuilder
{
    /**
     * @var CustomerFilter
     */
    private $instance;

    private function __construct(CustomerFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Customer Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new CustomerFilter());
    }

    /**
     * Sets creation source field.
     *
     * @param CustomerCreationSourceFilter|null $value
     */
    public function creationSource(?CustomerCreationSourceFilter $value): self
    {
        $this->instance->setCreationSource($value);
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param TimeRange|null $value
     */
    public function createdAt(?TimeRange $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param TimeRange|null $value
     */
    public function updatedAt(?TimeRange $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Sets email address field.
     *
     * @param CustomerTextFilter|null $value
     */
    public function emailAddress(?CustomerTextFilter $value): self
    {
        $this->instance->setEmailAddress($value);
        return $this;
    }

    /**
     * Sets phone number field.
     *
     * @param CustomerTextFilter|null $value
     */
    public function phoneNumber(?CustomerTextFilter $value): self
    {
        $this->instance->setPhoneNumber($value);
        return $this;
    }

    /**
     * Sets reference id field.
     *
     * @param CustomerTextFilter|null $value
     */
    public function referenceId(?CustomerTextFilter $value): self
    {
        $this->instance->setReferenceId($value);
        return $this;
    }

    /**
     * Sets group ids field.
     *
     * @param FilterValue|null $value
     */
    public function groupIds(?FilterValue $value): self
    {
        $this->instance->setGroupIds($value);
        return $this;
    }

    /**
     * Sets custom attribute field.
     *
     * @param CustomerCustomAttributeFilters|null $value
     */
    public function customAttribute(?CustomerCustomAttributeFilters $value): self
    {
        $this->instance->setCustomAttribute($value);
        return $this;
    }

    /**
     * Sets segment ids field.
     *
     * @param FilterValue|null $value
     */
    public function segmentIds(?FilterValue $value): self
    {
        $this->instance->setSegmentIds($value);
        return $this;
    }

    /**
     * Initializes a new Customer Filter object.
     */
    public function build(): CustomerFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
