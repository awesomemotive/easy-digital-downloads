<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyAccount;
use EDD\Vendor\Square\Models\LoyaltyAccountExpiringPointDeadline;
use EDD\Vendor\Square\Models\LoyaltyAccountMapping;

/**
 * Builder for model LoyaltyAccount
 *
 * @see LoyaltyAccount
 */
class LoyaltyAccountBuilder
{
    /**
     * @var LoyaltyAccount
     */
    private $instance;

    private function __construct(LoyaltyAccount $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Account Builder object.
     *
     * @param string $programId
     */
    public static function init(string $programId): self
    {
        return new self(new LoyaltyAccount($programId));
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
     * Sets balance field.
     *
     * @param int|null $value
     */
    public function balance(?int $value): self
    {
        $this->instance->setBalance($value);
        return $this;
    }

    /**
     * Sets lifetime points field.
     *
     * @param int|null $value
     */
    public function lifetimePoints(?int $value): self
    {
        $this->instance->setLifetimePoints($value);
        return $this;
    }

    /**
     * Sets customer id field.
     *
     * @param string|null $value
     */
    public function customerId(?string $value): self
    {
        $this->instance->setCustomerId($value);
        return $this;
    }

    /**
     * Unsets customer id field.
     */
    public function unsetCustomerId(): self
    {
        $this->instance->unsetCustomerId();
        return $this;
    }

    /**
     * Sets enrolled at field.
     *
     * @param string|null $value
     */
    public function enrolledAt(?string $value): self
    {
        $this->instance->setEnrolledAt($value);
        return $this;
    }

    /**
     * Unsets enrolled at field.
     */
    public function unsetEnrolledAt(): self
    {
        $this->instance->unsetEnrolledAt();
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
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Sets mapping field.
     *
     * @param LoyaltyAccountMapping|null $value
     */
    public function mapping(?LoyaltyAccountMapping $value): self
    {
        $this->instance->setMapping($value);
        return $this;
    }

    /**
     * Sets expiring point deadlines field.
     *
     * @param LoyaltyAccountExpiringPointDeadline[]|null $value
     */
    public function expiringPointDeadlines(?array $value): self
    {
        $this->instance->setExpiringPointDeadlines($value);
        return $this;
    }

    /**
     * Unsets expiring point deadlines field.
     */
    public function unsetExpiringPointDeadlines(): self
    {
        $this->instance->unsetExpiringPointDeadlines();
        return $this;
    }

    /**
     * Initializes a new Loyalty Account object.
     */
    public function build(): LoyaltyAccount
    {
        return CoreHelper::clone($this->instance);
    }
}
