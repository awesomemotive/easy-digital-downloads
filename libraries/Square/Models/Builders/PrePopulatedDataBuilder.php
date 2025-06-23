<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Address;
use EDD\Vendor\Square\Models\PrePopulatedData;

/**
 * Builder for model PrePopulatedData
 *
 * @see PrePopulatedData
 */
class PrePopulatedDataBuilder
{
    /**
     * @var PrePopulatedData
     */
    private $instance;

    private function __construct(PrePopulatedData $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Pre Populated Data Builder object.
     */
    public static function init(): self
    {
        return new self(new PrePopulatedData());
    }

    /**
     * Sets buyer email field.
     *
     * @param string|null $value
     */
    public function buyerEmail(?string $value): self
    {
        $this->instance->setBuyerEmail($value);
        return $this;
    }

    /**
     * Unsets buyer email field.
     */
    public function unsetBuyerEmail(): self
    {
        $this->instance->unsetBuyerEmail();
        return $this;
    }

    /**
     * Sets buyer phone number field.
     *
     * @param string|null $value
     */
    public function buyerPhoneNumber(?string $value): self
    {
        $this->instance->setBuyerPhoneNumber($value);
        return $this;
    }

    /**
     * Unsets buyer phone number field.
     */
    public function unsetBuyerPhoneNumber(): self
    {
        $this->instance->unsetBuyerPhoneNumber();
        return $this;
    }

    /**
     * Sets buyer address field.
     *
     * @param Address|null $value
     */
    public function buyerAddress(?Address $value): self
    {
        $this->instance->setBuyerAddress($value);
        return $this;
    }

    /**
     * Initializes a new Pre Populated Data object.
     */
    public function build(): PrePopulatedData
    {
        return CoreHelper::clone($this->instance);
    }
}
