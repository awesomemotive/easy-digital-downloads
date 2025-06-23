<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateMobileAuthorizationCodeRequest;

/**
 * Builder for model CreateMobileAuthorizationCodeRequest
 *
 * @see CreateMobileAuthorizationCodeRequest
 */
class CreateMobileAuthorizationCodeRequestBuilder
{
    /**
     * @var CreateMobileAuthorizationCodeRequest
     */
    private $instance;

    private function __construct(CreateMobileAuthorizationCodeRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Mobile Authorization Code Request Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateMobileAuthorizationCodeRequest());
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
     * Initializes a new Create Mobile Authorization Code Request object.
     */
    public function build(): CreateMobileAuthorizationCodeRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
