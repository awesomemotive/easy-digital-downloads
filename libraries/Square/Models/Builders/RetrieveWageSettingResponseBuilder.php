<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveWageSettingResponse;
use EDD\Vendor\Square\Models\WageSetting;

/**
 * Builder for model RetrieveWageSettingResponse
 *
 * @see RetrieveWageSettingResponse
 */
class RetrieveWageSettingResponseBuilder
{
    /**
     * @var RetrieveWageSettingResponse
     */
    private $instance;

    private function __construct(RetrieveWageSettingResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Wage Setting Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveWageSettingResponse());
    }

    /**
     * Sets wage setting field.
     *
     * @param WageSetting|null $value
     */
    public function wageSetting(?WageSetting $value): self
    {
        $this->instance->setWageSetting($value);
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
     * Initializes a new Retrieve Wage Setting Response object.
     */
    public function build(): RetrieveWageSettingResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
