<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyAccountMapping;

/**
 * Builder for model LoyaltyAccountMapping
 *
 * @see LoyaltyAccountMapping
 */
class LoyaltyAccountMappingBuilder
{
    /**
     * @var LoyaltyAccountMapping
     */
    private $instance;

    private function __construct(LoyaltyAccountMapping $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Account Mapping Builder object.
     */
    public static function init(): self
    {
        return new self(new LoyaltyAccountMapping());
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets phone number field.
     *
     * @param string|null $value
     */
    public function phoneNumber(?string $value): self
    {
        $this->instance->setPhoneNumber($value);
        return $this;
    }

    /**
     * Unsets phone number field.
     */
    public function unsetPhoneNumber(): self
    {
        $this->instance->unsetPhoneNumber();
        return $this;
    }

    /**
     * Initializes a new Loyalty Account Mapping object.
     */
    public function build(): LoyaltyAccountMapping
    {
        return CoreHelper::clone($this->instance);
    }
}
