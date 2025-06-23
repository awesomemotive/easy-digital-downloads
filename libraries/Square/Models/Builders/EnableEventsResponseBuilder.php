<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\EnableEventsResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model EnableEventsResponse
 *
 * @see EnableEventsResponse
 */
class EnableEventsResponseBuilder
{
    /**
     * @var EnableEventsResponse
     */
    private $instance;

    private function __construct(EnableEventsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Enable Events Response Builder object.
     */
    public static function init(): self
    {
        return new self(new EnableEventsResponse());
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
     * Initializes a new Enable Events Response object.
     */
    public function build(): EnableEventsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
