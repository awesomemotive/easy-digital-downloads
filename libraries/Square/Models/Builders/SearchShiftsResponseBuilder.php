<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\SearchShiftsResponse;
use EDD\Vendor\Square\Models\Shift;

/**
 * Builder for model SearchShiftsResponse
 *
 * @see SearchShiftsResponse
 */
class SearchShiftsResponseBuilder
{
    /**
     * @var SearchShiftsResponse
     */
    private $instance;

    private function __construct(SearchShiftsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Shifts Response Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchShiftsResponse());
    }

    /**
     * Sets shifts field.
     *
     * @param Shift[]|null $value
     */
    public function shifts(?array $value): self
    {
        $this->instance->setShifts($value);
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
     * Initializes a new Search Shifts Response object.
     */
    public function build(): SearchShiftsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
