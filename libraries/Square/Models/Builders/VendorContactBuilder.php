<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\VendorContact;

/**
 * Builder for model VendorContact
 *
 * @see VendorContact
 */
class VendorContactBuilder
{
    /**
     * @var VendorContact
     */
    private $instance;

    private function __construct(VendorContact $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Vendor Contact Builder object.
     *
     * @param int $ordinal
     */
    public static function init(int $ordinal): self
    {
        return new self(new VendorContact($ordinal));
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
     * Sets name field.
     *
     * @param string|null $value
     */
    public function name(?string $value): self
    {
        $this->instance->setName($value);
        return $this;
    }

    /**
     * Unsets name field.
     */
    public function unsetName(): self
    {
        $this->instance->unsetName();
        return $this;
    }

    /**
     * Sets email address field.
     *
     * @param string|null $value
     */
    public function emailAddress(?string $value): self
    {
        $this->instance->setEmailAddress($value);
        return $this;
    }

    /**
     * Unsets email address field.
     */
    public function unsetEmailAddress(): self
    {
        $this->instance->unsetEmailAddress();
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
     * Sets removed field.
     *
     * @param bool|null $value
     */
    public function removed(?bool $value): self
    {
        $this->instance->setRemoved($value);
        return $this;
    }

    /**
     * Unsets removed field.
     */
    public function unsetRemoved(): self
    {
        $this->instance->unsetRemoved();
        return $this;
    }

    /**
     * Initializes a new Vendor Contact object.
     */
    public function build(): VendorContact
    {
        return CoreHelper::clone($this->instance);
    }
}
