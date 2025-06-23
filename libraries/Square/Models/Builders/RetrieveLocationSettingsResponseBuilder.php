<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CheckoutLocationSettings;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveLocationSettingsResponse;

/**
 * Builder for model RetrieveLocationSettingsResponse
 *
 * @see RetrieveLocationSettingsResponse
 */
class RetrieveLocationSettingsResponseBuilder
{
    /**
     * @var RetrieveLocationSettingsResponse
     */
    private $instance;

    private function __construct(RetrieveLocationSettingsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Location Settings Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveLocationSettingsResponse());
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
     * Sets location settings field.
     *
     * @param CheckoutLocationSettings|null $value
     */
    public function locationSettings(?CheckoutLocationSettings $value): self
    {
        $this->instance->setLocationSettings($value);
        return $this;
    }

    /**
     * Initializes a new Retrieve Location Settings Response object.
     */
    public function build(): RetrieveLocationSettingsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
