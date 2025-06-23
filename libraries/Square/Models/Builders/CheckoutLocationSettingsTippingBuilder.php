<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CheckoutLocationSettingsTipping;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model CheckoutLocationSettingsTipping
 *
 * @see CheckoutLocationSettingsTipping
 */
class CheckoutLocationSettingsTippingBuilder
{
    /**
     * @var CheckoutLocationSettingsTipping
     */
    private $instance;

    private function __construct(CheckoutLocationSettingsTipping $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Checkout Location Settings Tipping Builder object.
     */
    public static function init(): self
    {
        return new self(new CheckoutLocationSettingsTipping());
    }

    /**
     * Sets percentages field.
     *
     * @param int[]|null $value
     */
    public function percentages(?array $value): self
    {
        $this->instance->setPercentages($value);
        return $this;
    }

    /**
     * Unsets percentages field.
     */
    public function unsetPercentages(): self
    {
        $this->instance->unsetPercentages();
        return $this;
    }

    /**
     * Sets smart tipping enabled field.
     *
     * @param bool|null $value
     */
    public function smartTippingEnabled(?bool $value): self
    {
        $this->instance->setSmartTippingEnabled($value);
        return $this;
    }

    /**
     * Unsets smart tipping enabled field.
     */
    public function unsetSmartTippingEnabled(): self
    {
        $this->instance->unsetSmartTippingEnabled();
        return $this;
    }

    /**
     * Sets default percent field.
     *
     * @param int|null $value
     */
    public function defaultPercent(?int $value): self
    {
        $this->instance->setDefaultPercent($value);
        return $this;
    }

    /**
     * Unsets default percent field.
     */
    public function unsetDefaultPercent(): self
    {
        $this->instance->unsetDefaultPercent();
        return $this;
    }

    /**
     * Sets smart tips field.
     *
     * @param Money[]|null $value
     */
    public function smartTips(?array $value): self
    {
        $this->instance->setSmartTips($value);
        return $this;
    }

    /**
     * Unsets smart tips field.
     */
    public function unsetSmartTips(): self
    {
        $this->instance->unsetSmartTips();
        return $this;
    }

    /**
     * Sets default smart tip field.
     *
     * @param Money|null $value
     */
    public function defaultSmartTip(?Money $value): self
    {
        $this->instance->setDefaultSmartTip($value);
        return $this;
    }

    /**
     * Initializes a new Checkout Location Settings Tipping object.
     */
    public function build(): CheckoutLocationSettingsTipping
    {
        return CoreHelper::clone($this->instance);
    }
}
