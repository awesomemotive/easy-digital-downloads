<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CashAppDetails;

/**
 * Builder for model CashAppDetails
 *
 * @see CashAppDetails
 */
class CashAppDetailsBuilder
{
    /**
     * @var CashAppDetails
     */
    private $instance;

    private function __construct(CashAppDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Cash App Details Builder object.
     */
    public static function init(): self
    {
        return new self(new CashAppDetails());
    }

    /**
     * Sets buyer full name field.
     *
     * @param string|null $value
     */
    public function buyerFullName(?string $value): self
    {
        $this->instance->setBuyerFullName($value);
        return $this;
    }

    /**
     * Unsets buyer full name field.
     */
    public function unsetBuyerFullName(): self
    {
        $this->instance->unsetBuyerFullName();
        return $this;
    }

    /**
     * Sets buyer country code field.
     *
     * @param string|null $value
     */
    public function buyerCountryCode(?string $value): self
    {
        $this->instance->setBuyerCountryCode($value);
        return $this;
    }

    /**
     * Unsets buyer country code field.
     */
    public function unsetBuyerCountryCode(): self
    {
        $this->instance->unsetBuyerCountryCode();
        return $this;
    }

    /**
     * Sets buyer cashtag field.
     *
     * @param string|null $value
     */
    public function buyerCashtag(?string $value): self
    {
        $this->instance->setBuyerCashtag($value);
        return $this;
    }

    /**
     * Initializes a new Cash App Details object.
     */
    public function build(): CashAppDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
