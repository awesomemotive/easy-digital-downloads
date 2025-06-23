<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateLoyaltyAccountResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\LoyaltyAccount;

/**
 * Builder for model CreateLoyaltyAccountResponse
 *
 * @see CreateLoyaltyAccountResponse
 */
class CreateLoyaltyAccountResponseBuilder
{
    /**
     * @var CreateLoyaltyAccountResponse
     */
    private $instance;

    private function __construct(CreateLoyaltyAccountResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Loyalty Account Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateLoyaltyAccountResponse());
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
     * Sets loyalty account field.
     *
     * @param LoyaltyAccount|null $value
     */
    public function loyaltyAccount(?LoyaltyAccount $value): self
    {
        $this->instance->setLoyaltyAccount($value);
        return $this;
    }

    /**
     * Initializes a new Create Loyalty Account Response object.
     */
    public function build(): CreateLoyaltyAccountResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
