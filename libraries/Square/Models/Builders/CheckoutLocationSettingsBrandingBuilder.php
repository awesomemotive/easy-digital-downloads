<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CheckoutLocationSettingsBranding;

/**
 * Builder for model CheckoutLocationSettingsBranding
 *
 * @see CheckoutLocationSettingsBranding
 */
class CheckoutLocationSettingsBrandingBuilder
{
    /**
     * @var CheckoutLocationSettingsBranding
     */
    private $instance;

    private function __construct(CheckoutLocationSettingsBranding $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Checkout Location Settings Branding Builder object.
     */
    public static function init(): self
    {
        return new self(new CheckoutLocationSettingsBranding());
    }

    /**
     * Sets header type field.
     *
     * @param string|null $value
     */
    public function headerType(?string $value): self
    {
        $this->instance->setHeaderType($value);
        return $this;
    }

    /**
     * Sets button color field.
     *
     * @param string|null $value
     */
    public function buttonColor(?string $value): self
    {
        $this->instance->setButtonColor($value);
        return $this;
    }

    /**
     * Unsets button color field.
     */
    public function unsetButtonColor(): self
    {
        $this->instance->unsetButtonColor();
        return $this;
    }

    /**
     * Sets button shape field.
     *
     * @param string|null $value
     */
    public function buttonShape(?string $value): self
    {
        $this->instance->setButtonShape($value);
        return $this;
    }

    /**
     * Initializes a new Checkout Location Settings Branding object.
     */
    public function build(): CheckoutLocationSettingsBranding
    {
        return CoreHelper::clone($this->instance);
    }
}
