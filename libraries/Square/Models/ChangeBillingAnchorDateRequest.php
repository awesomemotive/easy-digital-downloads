<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Defines input parameters in a request to the
 * [ChangeBillingAnchorDate]($e/Subscriptions/ChangeBillingAnchorDate) endpoint.
 */
class ChangeBillingAnchorDateRequest implements \JsonSerializable
{
    /**
     * @var array
     */
    private $monthlyBillingAnchorDate = [];

    /**
     * @var array
     */
    private $effectiveDate = [];

    /**
     * Returns Monthly Billing Anchor Date.
     * The anchor day for the billing cycle.
     */
    public function getMonthlyBillingAnchorDate(): ?int
    {
        if (count($this->monthlyBillingAnchorDate) == 0) {
            return null;
        }
        return $this->monthlyBillingAnchorDate['value'];
    }

    /**
     * Sets Monthly Billing Anchor Date.
     * The anchor day for the billing cycle.
     *
     * @maps monthly_billing_anchor_date
     */
    public function setMonthlyBillingAnchorDate(?int $monthlyBillingAnchorDate): void
    {
        $this->monthlyBillingAnchorDate['value'] = $monthlyBillingAnchorDate;
    }

    /**
     * Unsets Monthly Billing Anchor Date.
     * The anchor day for the billing cycle.
     */
    public function unsetMonthlyBillingAnchorDate(): void
    {
        $this->monthlyBillingAnchorDate = [];
    }

    /**
     * Returns Effective Date.
     * The `YYYY-MM-DD`-formatted date when the scheduled `BILLING_ANCHOR_CHANGE` action takes
     * place on the subscription.
     *
     * When this date is unspecified or falls within the current billing cycle, the billing anchor date
     * is changed immediately.
     */
    public function getEffectiveDate(): ?string
    {
        if (count($this->effectiveDate) == 0) {
            return null;
        }
        return $this->effectiveDate['value'];
    }

    /**
     * Sets Effective Date.
     * The `YYYY-MM-DD`-formatted date when the scheduled `BILLING_ANCHOR_CHANGE` action takes
     * place on the subscription.
     *
     * When this date is unspecified or falls within the current billing cycle, the billing anchor date
     * is changed immediately.
     *
     * @maps effective_date
     */
    public function setEffectiveDate(?string $effectiveDate): void
    {
        $this->effectiveDate['value'] = $effectiveDate;
    }

    /**
     * Unsets Effective Date.
     * The `YYYY-MM-DD`-formatted date when the scheduled `BILLING_ANCHOR_CHANGE` action takes
     * place on the subscription.
     *
     * When this date is unspecified or falls within the current billing cycle, the billing anchor date
     * is changed immediately.
     */
    public function unsetEffectiveDate(): void
    {
        $this->effectiveDate = [];
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
        if (!empty($this->monthlyBillingAnchorDate)) {
            $json['monthly_billing_anchor_date'] = $this->monthlyBillingAnchorDate['value'];
        }
        if (!empty($this->effectiveDate)) {
            $json['effective_date']              = $this->effectiveDate['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
