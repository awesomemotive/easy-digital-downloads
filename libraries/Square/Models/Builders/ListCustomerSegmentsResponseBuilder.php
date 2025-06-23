<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerSegment;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListCustomerSegmentsResponse;

/**
 * Builder for model ListCustomerSegmentsResponse
 *
 * @see ListCustomerSegmentsResponse
 */
class ListCustomerSegmentsResponseBuilder
{
    /**
     * @var ListCustomerSegmentsResponse
     */
    private $instance;

    private function __construct(ListCustomerSegmentsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Customer Segments Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListCustomerSegmentsResponse());
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Sets segments field.
     *
     * @param CustomerSegment[]|null $value
     */
    public function segments(?array $value): self
    {
        $this->instance->setSegments($value);
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
     * Initializes a new List Customer Segments Response object.
     */
    public function build(): ListCustomerSegmentsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
