<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RegisterDomainResponse;

/**
 * Builder for model RegisterDomainResponse
 *
 * @see RegisterDomainResponse
 */
class RegisterDomainResponseBuilder
{
    /**
     * @var RegisterDomainResponse
     */
    private $instance;

    private function __construct(RegisterDomainResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Register Domain Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RegisterDomainResponse());
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
     * Initializes a new Register Domain Response object.
     */
    public function build(): RegisterDomainResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
