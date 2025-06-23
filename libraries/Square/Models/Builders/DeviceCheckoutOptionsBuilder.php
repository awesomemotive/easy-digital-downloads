<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceCheckoutOptions;
use EDD\Vendor\Square\Models\TipSettings;

/**
 * Builder for model DeviceCheckoutOptions
 *
 * @see DeviceCheckoutOptions
 */
class DeviceCheckoutOptionsBuilder
{
    /**
     * @var DeviceCheckoutOptions
     */
    private $instance;

    private function __construct(DeviceCheckoutOptions $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Device Checkout Options Builder object.
     *
     * @param string $deviceId
     */
    public static function init(string $deviceId): self
    {
        return new self(new DeviceCheckoutOptions($deviceId));
    }

    /**
     * Sets skip receipt screen field.
     *
     * @param bool|null $value
     */
    public function skipReceiptScreen(?bool $value): self
    {
        $this->instance->setSkipReceiptScreen($value);
        return $this;
    }

    /**
     * Unsets skip receipt screen field.
     */
    public function unsetSkipReceiptScreen(): self
    {
        $this->instance->unsetSkipReceiptScreen();
        return $this;
    }

    /**
     * Sets collect signature field.
     *
     * @param bool|null $value
     */
    public function collectSignature(?bool $value): self
    {
        $this->instance->setCollectSignature($value);
        return $this;
    }

    /**
     * Unsets collect signature field.
     */
    public function unsetCollectSignature(): self
    {
        $this->instance->unsetCollectSignature();
        return $this;
    }

    /**
     * Sets tip settings field.
     *
     * @param TipSettings|null $value
     */
    public function tipSettings(?TipSettings $value): self
    {
        $this->instance->setTipSettings($value);
        return $this;
    }

    /**
     * Sets show itemized cart field.
     *
     * @param bool|null $value
     */
    public function showItemizedCart(?bool $value): self
    {
        $this->instance->setShowItemizedCart($value);
        return $this;
    }

    /**
     * Unsets show itemized cart field.
     */
    public function unsetShowItemizedCart(): self
    {
        $this->instance->unsetShowItemizedCart();
        return $this;
    }

    /**
     * Initializes a new Device Checkout Options object.
     */
    public function build(): DeviceCheckoutOptions
    {
        return CoreHelper::clone($this->instance);
    }
}
