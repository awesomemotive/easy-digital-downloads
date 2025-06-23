<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\PaymentBalanceActivityAppFeeRefundDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityAppFeeRevenueDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityAutomaticSavingsDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityAutomaticSavingsReversedDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityChargeDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityDepositFeeDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityDepositFeeReversedDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityDisputeDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityFeeDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityFreeProcessingDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityHoldAdjustmentDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityOpenDisputeDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityOtherAdjustmentDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityOtherDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityRefundDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityReleaseAdjustmentDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityReserveHoldDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityReserveReleaseDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivitySquareCapitalPaymentDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivitySquareCapitalReversedPaymentDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivitySquarePayrollTransferDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivitySquarePayrollTransferReversedDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityTaxOnFeeDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityThirdPartyFeeDetail;
use EDD\Vendor\Square\Models\PaymentBalanceActivityThirdPartyFeeRefundDetail;
use EDD\Vendor\Square\Models\PayoutEntry;

/**
 * Builder for model PayoutEntry
 *
 * @see PayoutEntry
 */
class PayoutEntryBuilder
{
    /**
     * @var PayoutEntry
     */
    private $instance;

    private function __construct(PayoutEntry $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payout Entry Builder object.
     *
     * @param string $id
     * @param string $payoutId
     */
    public static function init(string $id, string $payoutId): self
    {
        return new self(new PayoutEntry($id, $payoutId));
    }

    /**
     * Sets effective at field.
     *
     * @param string|null $value
     */
    public function effectiveAt(?string $value): self
    {
        $this->instance->setEffectiveAt($value);
        return $this;
    }

    /**
     * Unsets effective at field.
     */
    public function unsetEffectiveAt(): self
    {
        $this->instance->unsetEffectiveAt();
        return $this;
    }

    /**
     * Sets type field.
     *
     * @param string|null $value
     */
    public function type(?string $value): self
    {
        $this->instance->setType($value);
        return $this;
    }

    /**
     * Sets gross amount money field.
     *
     * @param Money|null $value
     */
    public function grossAmountMoney(?Money $value): self
    {
        $this->instance->setGrossAmountMoney($value);
        return $this;
    }

    /**
     * Sets fee amount money field.
     *
     * @param Money|null $value
     */
    public function feeAmountMoney(?Money $value): self
    {
        $this->instance->setFeeAmountMoney($value);
        return $this;
    }

    /**
     * Sets net amount money field.
     *
     * @param Money|null $value
     */
    public function netAmountMoney(?Money $value): self
    {
        $this->instance->setNetAmountMoney($value);
        return $this;
    }

    /**
     * Sets type app fee revenue details field.
     *
     * @param PaymentBalanceActivityAppFeeRevenueDetail|null $value
     */
    public function typeAppFeeRevenueDetails(?PaymentBalanceActivityAppFeeRevenueDetail $value): self
    {
        $this->instance->setTypeAppFeeRevenueDetails($value);
        return $this;
    }

    /**
     * Sets type app fee refund details field.
     *
     * @param PaymentBalanceActivityAppFeeRefundDetail|null $value
     */
    public function typeAppFeeRefundDetails(?PaymentBalanceActivityAppFeeRefundDetail $value): self
    {
        $this->instance->setTypeAppFeeRefundDetails($value);
        return $this;
    }

    /**
     * Sets type automatic savings details field.
     *
     * @param PaymentBalanceActivityAutomaticSavingsDetail|null $value
     */
    public function typeAutomaticSavingsDetails(?PaymentBalanceActivityAutomaticSavingsDetail $value): self
    {
        $this->instance->setTypeAutomaticSavingsDetails($value);
        return $this;
    }

    /**
     * Sets type automatic savings reversed details field.
     *
     * @param PaymentBalanceActivityAutomaticSavingsReversedDetail|null $value
     */
    public function typeAutomaticSavingsReversedDetails(
        ?PaymentBalanceActivityAutomaticSavingsReversedDetail $value
    ): self {
        $this->instance->setTypeAutomaticSavingsReversedDetails($value);
        return $this;
    }

    /**
     * Sets type charge details field.
     *
     * @param PaymentBalanceActivityChargeDetail|null $value
     */
    public function typeChargeDetails(?PaymentBalanceActivityChargeDetail $value): self
    {
        $this->instance->setTypeChargeDetails($value);
        return $this;
    }

    /**
     * Sets type deposit fee details field.
     *
     * @param PaymentBalanceActivityDepositFeeDetail|null $value
     */
    public function typeDepositFeeDetails(?PaymentBalanceActivityDepositFeeDetail $value): self
    {
        $this->instance->setTypeDepositFeeDetails($value);
        return $this;
    }

    /**
     * Sets type deposit fee reversed details field.
     *
     * @param PaymentBalanceActivityDepositFeeReversedDetail|null $value
     */
    public function typeDepositFeeReversedDetails(?PaymentBalanceActivityDepositFeeReversedDetail $value): self
    {
        $this->instance->setTypeDepositFeeReversedDetails($value);
        return $this;
    }

    /**
     * Sets type dispute details field.
     *
     * @param PaymentBalanceActivityDisputeDetail|null $value
     */
    public function typeDisputeDetails(?PaymentBalanceActivityDisputeDetail $value): self
    {
        $this->instance->setTypeDisputeDetails($value);
        return $this;
    }

    /**
     * Sets type fee details field.
     *
     * @param PaymentBalanceActivityFeeDetail|null $value
     */
    public function typeFeeDetails(?PaymentBalanceActivityFeeDetail $value): self
    {
        $this->instance->setTypeFeeDetails($value);
        return $this;
    }

    /**
     * Sets type free processing details field.
     *
     * @param PaymentBalanceActivityFreeProcessingDetail|null $value
     */
    public function typeFreeProcessingDetails(?PaymentBalanceActivityFreeProcessingDetail $value): self
    {
        $this->instance->setTypeFreeProcessingDetails($value);
        return $this;
    }

    /**
     * Sets type hold adjustment details field.
     *
     * @param PaymentBalanceActivityHoldAdjustmentDetail|null $value
     */
    public function typeHoldAdjustmentDetails(?PaymentBalanceActivityHoldAdjustmentDetail $value): self
    {
        $this->instance->setTypeHoldAdjustmentDetails($value);
        return $this;
    }

    /**
     * Sets type open dispute details field.
     *
     * @param PaymentBalanceActivityOpenDisputeDetail|null $value
     */
    public function typeOpenDisputeDetails(?PaymentBalanceActivityOpenDisputeDetail $value): self
    {
        $this->instance->setTypeOpenDisputeDetails($value);
        return $this;
    }

    /**
     * Sets type other details field.
     *
     * @param PaymentBalanceActivityOtherDetail|null $value
     */
    public function typeOtherDetails(?PaymentBalanceActivityOtherDetail $value): self
    {
        $this->instance->setTypeOtherDetails($value);
        return $this;
    }

    /**
     * Sets type other adjustment details field.
     *
     * @param PaymentBalanceActivityOtherAdjustmentDetail|null $value
     */
    public function typeOtherAdjustmentDetails(?PaymentBalanceActivityOtherAdjustmentDetail $value): self
    {
        $this->instance->setTypeOtherAdjustmentDetails($value);
        return $this;
    }

    /**
     * Sets type refund details field.
     *
     * @param PaymentBalanceActivityRefundDetail|null $value
     */
    public function typeRefundDetails(?PaymentBalanceActivityRefundDetail $value): self
    {
        $this->instance->setTypeRefundDetails($value);
        return $this;
    }

    /**
     * Sets type release adjustment details field.
     *
     * @param PaymentBalanceActivityReleaseAdjustmentDetail|null $value
     */
    public function typeReleaseAdjustmentDetails(?PaymentBalanceActivityReleaseAdjustmentDetail $value): self
    {
        $this->instance->setTypeReleaseAdjustmentDetails($value);
        return $this;
    }

    /**
     * Sets type reserve hold details field.
     *
     * @param PaymentBalanceActivityReserveHoldDetail|null $value
     */
    public function typeReserveHoldDetails(?PaymentBalanceActivityReserveHoldDetail $value): self
    {
        $this->instance->setTypeReserveHoldDetails($value);
        return $this;
    }

    /**
     * Sets type reserve release details field.
     *
     * @param PaymentBalanceActivityReserveReleaseDetail|null $value
     */
    public function typeReserveReleaseDetails(?PaymentBalanceActivityReserveReleaseDetail $value): self
    {
        $this->instance->setTypeReserveReleaseDetails($value);
        return $this;
    }

    /**
     * Sets type square capital payment details field.
     *
     * @param PaymentBalanceActivitySquareCapitalPaymentDetail|null $value
     */
    public function typeSquareCapitalPaymentDetails(?PaymentBalanceActivitySquareCapitalPaymentDetail $value): self
    {
        $this->instance->setTypeSquareCapitalPaymentDetails($value);
        return $this;
    }

    /**
     * Sets type square capital reversed payment details field.
     *
     * @param PaymentBalanceActivitySquareCapitalReversedPaymentDetail|null $value
     */
    public function typeSquareCapitalReversedPaymentDetails(
        ?PaymentBalanceActivitySquareCapitalReversedPaymentDetail $value
    ): self {
        $this->instance->setTypeSquareCapitalReversedPaymentDetails($value);
        return $this;
    }

    /**
     * Sets type tax on fee details field.
     *
     * @param PaymentBalanceActivityTaxOnFeeDetail|null $value
     */
    public function typeTaxOnFeeDetails(?PaymentBalanceActivityTaxOnFeeDetail $value): self
    {
        $this->instance->setTypeTaxOnFeeDetails($value);
        return $this;
    }

    /**
     * Sets type third party fee details field.
     *
     * @param PaymentBalanceActivityThirdPartyFeeDetail|null $value
     */
    public function typeThirdPartyFeeDetails(?PaymentBalanceActivityThirdPartyFeeDetail $value): self
    {
        $this->instance->setTypeThirdPartyFeeDetails($value);
        return $this;
    }

    /**
     * Sets type third party fee refund details field.
     *
     * @param PaymentBalanceActivityThirdPartyFeeRefundDetail|null $value
     */
    public function typeThirdPartyFeeRefundDetails(?PaymentBalanceActivityThirdPartyFeeRefundDetail $value): self
    {
        $this->instance->setTypeThirdPartyFeeRefundDetails($value);
        return $this;
    }

    /**
     * Sets type square payroll transfer details field.
     *
     * @param PaymentBalanceActivitySquarePayrollTransferDetail|null $value
     */
    public function typeSquarePayrollTransferDetails(?PaymentBalanceActivitySquarePayrollTransferDetail $value): self
    {
        $this->instance->setTypeSquarePayrollTransferDetails($value);
        return $this;
    }

    /**
     * Sets type square payroll transfer reversed details field.
     *
     * @param PaymentBalanceActivitySquarePayrollTransferReversedDetail|null $value
     */
    public function typeSquarePayrollTransferReversedDetails(
        ?PaymentBalanceActivitySquarePayrollTransferReversedDetail $value
    ): self {
        $this->instance->setTypeSquarePayrollTransferReversedDetails($value);
        return $this;
    }

    /**
     * Initializes a new Payout Entry object.
     */
    public function build(): PayoutEntry
    {
        return CoreHelper::clone($this->instance);
    }
}
