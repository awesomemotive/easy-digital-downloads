<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class CheckoutLocationSettingsTipping implements \JsonSerializable
{
    /**
     * @var array
     */
    private $percentages = [];

    /**
     * @var array
     */
    private $smartTippingEnabled = [];

    /**
     * @var array
     */
    private $defaultPercent = [];

    /**
     * @var array
     */
    private $smartTips = [];

    /**
     * @var Money|null
     */
    private $defaultSmartTip;

    /**
     * Returns Percentages.
     * Set three custom percentage amounts that buyers can select at checkout. If Smart Tip is enabled,
     * this only applies to transactions totaling $10 or more.
     *
     * @return int[]|null
     */
    public function getPercentages(): ?array
    {
        if (count($this->percentages) == 0) {
            return null;
        }
        return $this->percentages['value'];
    }

    /**
     * Sets Percentages.
     * Set three custom percentage amounts that buyers can select at checkout. If Smart Tip is enabled,
     * this only applies to transactions totaling $10 or more.
     *
     * @maps percentages
     *
     * @param int[]|null $percentages
     */
    public function setPercentages(?array $percentages): void
    {
        $this->percentages['value'] = $percentages;
    }

    /**
     * Unsets Percentages.
     * Set three custom percentage amounts that buyers can select at checkout. If Smart Tip is enabled,
     * this only applies to transactions totaling $10 or more.
     */
    public function unsetPercentages(): void
    {
        $this->percentages = [];
    }

    /**
     * Returns Smart Tipping Enabled.
     * Enables Smart Tip Amounts. If Smart Tip Amounts is enabled, tipping works as follows:
     * If a transaction is less than $10, the available tipping options include No Tip, $1, $2, or $3.
     * If a transaction is $10 or more, the available tipping options include No Tip, 15%, 20%, or 25%.
     * You can set custom percentage amounts with the `percentages` field.
     */
    public function getSmartTippingEnabled(): ?bool
    {
        if (count($this->smartTippingEnabled) == 0) {
            return null;
        }
        return $this->smartTippingEnabled['value'];
    }

    /**
     * Sets Smart Tipping Enabled.
     * Enables Smart Tip Amounts. If Smart Tip Amounts is enabled, tipping works as follows:
     * If a transaction is less than $10, the available tipping options include No Tip, $1, $2, or $3.
     * If a transaction is $10 or more, the available tipping options include No Tip, 15%, 20%, or 25%.
     * You can set custom percentage amounts with the `percentages` field.
     *
     * @maps smart_tipping_enabled
     */
    public function setSmartTippingEnabled(?bool $smartTippingEnabled): void
    {
        $this->smartTippingEnabled['value'] = $smartTippingEnabled;
    }

    /**
     * Unsets Smart Tipping Enabled.
     * Enables Smart Tip Amounts. If Smart Tip Amounts is enabled, tipping works as follows:
     * If a transaction is less than $10, the available tipping options include No Tip, $1, $2, or $3.
     * If a transaction is $10 or more, the available tipping options include No Tip, 15%, 20%, or 25%.
     * You can set custom percentage amounts with the `percentages` field.
     */
    public function unsetSmartTippingEnabled(): void
    {
        $this->smartTippingEnabled = [];
    }

    /**
     * Returns Default Percent.
     * Set the pre-selected percentage amounts that appear at checkout. If Smart Tip is enabled, this only
     * applies to transactions totaling $10 or more.
     */
    public function getDefaultPercent(): ?int
    {
        if (count($this->defaultPercent) == 0) {
            return null;
        }
        return $this->defaultPercent['value'];
    }

    /**
     * Sets Default Percent.
     * Set the pre-selected percentage amounts that appear at checkout. If Smart Tip is enabled, this only
     * applies to transactions totaling $10 or more.
     *
     * @maps default_percent
     */
    public function setDefaultPercent(?int $defaultPercent): void
    {
        $this->defaultPercent['value'] = $defaultPercent;
    }

    /**
     * Unsets Default Percent.
     * Set the pre-selected percentage amounts that appear at checkout. If Smart Tip is enabled, this only
     * applies to transactions totaling $10 or more.
     */
    public function unsetDefaultPercent(): void
    {
        $this->defaultPercent = [];
    }

    /**
     * Returns Smart Tips.
     * Show the Smart Tip Amounts for this location.
     *
     * @return Money[]|null
     */
    public function getSmartTips(): ?array
    {
        if (count($this->smartTips) == 0) {
            return null;
        }
        return $this->smartTips['value'];
    }

    /**
     * Sets Smart Tips.
     * Show the Smart Tip Amounts for this location.
     *
     * @maps smart_tips
     *
     * @param Money[]|null $smartTips
     */
    public function setSmartTips(?array $smartTips): void
    {
        $this->smartTips['value'] = $smartTips;
    }

    /**
     * Unsets Smart Tips.
     * Show the Smart Tip Amounts for this location.
     */
    public function unsetSmartTips(): void
    {
        $this->smartTips = [];
    }

    /**
     * Returns Default Smart Tip.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getDefaultSmartTip(): ?Money
    {
        return $this->defaultSmartTip;
    }

    /**
     * Sets Default Smart Tip.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps default_smart_tip
     */
    public function setDefaultSmartTip(?Money $defaultSmartTip): void
    {
        $this->defaultSmartTip = $defaultSmartTip;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        if (!empty($this->percentages)) {
            $json['percentages']           = $this->percentages['value'];
        }
        if (!empty($this->smartTippingEnabled)) {
            $json['smart_tipping_enabled'] = $this->smartTippingEnabled['value'];
        }
        if (!empty($this->defaultPercent)) {
            $json['default_percent']       = $this->defaultPercent['value'];
        }
        if (!empty($this->smartTips)) {
            $json['smart_tips']            = $this->smartTips['value'];
        }
        if (isset($this->defaultSmartTip)) {
            $json['default_smart_tip']     = $this->defaultSmartTip;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
