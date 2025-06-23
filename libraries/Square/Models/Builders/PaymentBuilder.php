<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Address;
use EDD\Vendor\Square\Models\ApplicationDetails;
use EDD\Vendor\Square\Models\BankAccountPaymentDetails;
use EDD\Vendor\Square\Models\BuyNowPayLaterDetails;
use EDD\Vendor\Square\Models\CardPaymentDetails;
use EDD\Vendor\Square\Models\CashPaymentDetails;
use EDD\Vendor\Square\Models\DeviceDetails;
use EDD\Vendor\Square\Models\DigitalWalletDetails;
use EDD\Vendor\Square\Models\ExternalPaymentDetails;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\OfflinePaymentDetails;
use EDD\Vendor\Square\Models\Payment;
use EDD\Vendor\Square\Models\ProcessingFee;
use EDD\Vendor\Square\Models\RiskEvaluation;
use EDD\Vendor\Square\Models\SquareAccountDetails;

/**
 * Builder for model Payment
 *
 * @see Payment
 */
class PaymentBuilder
{
    /**
     * @var Payment
     */
    private $instance;

    private function __construct(Payment $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Builder object.
     */
    public static function init(): self
    {
        return new self(new Payment());
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
     * Sets amount money field.
     *
     * @param Money|null $value
     */
    public function amountMoney(?Money $value): self
    {
        $this->instance->setAmountMoney($value);
        return $this;
    }

    /**
     * Sets tip money field.
     *
     * @param Money|null $value
     */
    public function tipMoney(?Money $value): self
    {
        $this->instance->setTipMoney($value);
        return $this;
    }

    /**
     * Sets total money field.
     *
     * @param Money|null $value
     */
    public function totalMoney(?Money $value): self
    {
        $this->instance->setTotalMoney($value);
        return $this;
    }

    /**
     * Sets app fee money field.
     *
     * @param Money|null $value
     */
    public function appFeeMoney(?Money $value): self
    {
        $this->instance->setAppFeeMoney($value);
        return $this;
    }

    /**
     * Sets approved money field.
     *
     * @param Money|null $value
     */
    public function approvedMoney(?Money $value): self
    {
        $this->instance->setApprovedMoney($value);
        return $this;
    }

    /**
     * Sets processing fee field.
     *
     * @param ProcessingFee[]|null $value
     */
    public function processingFee(?array $value): self
    {
        $this->instance->setProcessingFee($value);
        return $this;
    }

    /**
     * Sets refunded money field.
     *
     * @param Money|null $value
     */
    public function refundedMoney(?Money $value): self
    {
        $this->instance->setRefundedMoney($value);
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Sets delay duration field.
     *
     * @param string|null $value
     */
    public function delayDuration(?string $value): self
    {
        $this->instance->setDelayDuration($value);
        return $this;
    }

    /**
     * Sets delay action field.
     *
     * @param string|null $value
     */
    public function delayAction(?string $value): self
    {
        $this->instance->setDelayAction($value);
        return $this;
    }

    /**
     * Unsets delay action field.
     */
    public function unsetDelayAction(): self
    {
        $this->instance->unsetDelayAction();
        return $this;
    }

    /**
     * Sets delayed until field.
     *
     * @param string|null $value
     */
    public function delayedUntil(?string $value): self
    {
        $this->instance->setDelayedUntil($value);
        return $this;
    }

    /**
     * Sets source type field.
     *
     * @param string|null $value
     */
    public function sourceType(?string $value): self
    {
        $this->instance->setSourceType($value);
        return $this;
    }

    /**
     * Sets card details field.
     *
     * @param CardPaymentDetails|null $value
     */
    public function cardDetails(?CardPaymentDetails $value): self
    {
        $this->instance->setCardDetails($value);
        return $this;
    }

    /**
     * Sets cash details field.
     *
     * @param CashPaymentDetails|null $value
     */
    public function cashDetails(?CashPaymentDetails $value): self
    {
        $this->instance->setCashDetails($value);
        return $this;
    }

    /**
     * Sets bank account details field.
     *
     * @param BankAccountPaymentDetails|null $value
     */
    public function bankAccountDetails(?BankAccountPaymentDetails $value): self
    {
        $this->instance->setBankAccountDetails($value);
        return $this;
    }

    /**
     * Sets external details field.
     *
     * @param ExternalPaymentDetails|null $value
     */
    public function externalDetails(?ExternalPaymentDetails $value): self
    {
        $this->instance->setExternalDetails($value);
        return $this;
    }

    /**
     * Sets wallet details field.
     *
     * @param DigitalWalletDetails|null $value
     */
    public function walletDetails(?DigitalWalletDetails $value): self
    {
        $this->instance->setWalletDetails($value);
        return $this;
    }

    /**
     * Sets buy now pay later details field.
     *
     * @param BuyNowPayLaterDetails|null $value
     */
    public function buyNowPayLaterDetails(?BuyNowPayLaterDetails $value): self
    {
        $this->instance->setBuyNowPayLaterDetails($value);
        return $this;
    }

    /**
     * Sets square account details field.
     *
     * @param SquareAccountDetails|null $value
     */
    public function squareAccountDetails(?SquareAccountDetails $value): self
    {
        $this->instance->setSquareAccountDetails($value);
        return $this;
    }

    /**
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Sets order id field.
     *
     * @param string|null $value
     */
    public function orderId(?string $value): self
    {
        $this->instance->setOrderId($value);
        return $this;
    }

    /**
     * Sets reference id field.
     *
     * @param string|null $value
     */
    public function referenceId(?string $value): self
    {
        $this->instance->setReferenceId($value);
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
     * Sets employee id field.
     *
     * @param string|null $value
     */
    public function employeeId(?string $value): self
    {
        $this->instance->setEmployeeId($value);
        return $this;
    }

    /**
     * Sets team member id field.
     *
     * @param string|null $value
     */
    public function teamMemberId(?string $value): self
    {
        $this->instance->setTeamMemberId($value);
        return $this;
    }

    /**
     * Unsets team member id field.
     */
    public function unsetTeamMemberId(): self
    {
        $this->instance->unsetTeamMemberId();
        return $this;
    }

    /**
     * Sets refund ids field.
     *
     * @param string[]|null $value
     */
    public function refundIds(?array $value): self
    {
        $this->instance->setRefundIds($value);
        return $this;
    }

    /**
     * Sets risk evaluation field.
     *
     * @param RiskEvaluation|null $value
     */
    public function riskEvaluation(?RiskEvaluation $value): self
    {
        $this->instance->setRiskEvaluation($value);
        return $this;
    }

    /**
     * Sets terminal checkout id field.
     *
     * @param string|null $value
     */
    public function terminalCheckoutId(?string $value): self
    {
        $this->instance->setTerminalCheckoutId($value);
        return $this;
    }

    /**
     * Sets buyer email address field.
     *
     * @param string|null $value
     */
    public function buyerEmailAddress(?string $value): self
    {
        $this->instance->setBuyerEmailAddress($value);
        return $this;
    }

    /**
     * Sets billing address field.
     *
     * @param Address|null $value
     */
    public function billingAddress(?Address $value): self
    {
        $this->instance->setBillingAddress($value);
        return $this;
    }

    /**
     * Sets shipping address field.
     *
     * @param Address|null $value
     */
    public function shippingAddress(?Address $value): self
    {
        $this->instance->setShippingAddress($value);
        return $this;
    }

    /**
     * Sets note field.
     *
     * @param string|null $value
     */
    public function note(?string $value): self
    {
        $this->instance->setNote($value);
        return $this;
    }

    /**
     * Sets statement description identifier field.
     *
     * @param string|null $value
     */
    public function statementDescriptionIdentifier(?string $value): self
    {
        $this->instance->setStatementDescriptionIdentifier($value);
        return $this;
    }

    /**
     * Sets capabilities field.
     *
     * @param string[]|null $value
     */
    public function capabilities(?array $value): self
    {
        $this->instance->setCapabilities($value);
        return $this;
    }

    /**
     * Sets receipt number field.
     *
     * @param string|null $value
     */
    public function receiptNumber(?string $value): self
    {
        $this->instance->setReceiptNumber($value);
        return $this;
    }

    /**
     * Sets receipt url field.
     *
     * @param string|null $value
     */
    public function receiptUrl(?string $value): self
    {
        $this->instance->setReceiptUrl($value);
        return $this;
    }

    /**
     * Sets device details field.
     *
     * @param DeviceDetails|null $value
     */
    public function deviceDetails(?DeviceDetails $value): self
    {
        $this->instance->setDeviceDetails($value);
        return $this;
    }

    /**
     * Sets application details field.
     *
     * @param ApplicationDetails|null $value
     */
    public function applicationDetails(?ApplicationDetails $value): self
    {
        $this->instance->setApplicationDetails($value);
        return $this;
    }

    /**
     * Sets is offline payment field.
     *
     * @param bool|null $value
     */
    public function isOfflinePayment(?bool $value): self
    {
        $this->instance->setIsOfflinePayment($value);
        return $this;
    }

    /**
     * Sets offline payment details field.
     *
     * @param OfflinePaymentDetails|null $value
     */
    public function offlinePaymentDetails(?OfflinePaymentDetails $value): self
    {
        $this->instance->setOfflinePaymentDetails($value);
        return $this;
    }

    /**
     * Sets version token field.
     *
     * @param string|null $value
     */
    public function versionToken(?string $value): self
    {
        $this->instance->setVersionToken($value);
        return $this;
    }

    /**
     * Unsets version token field.
     */
    public function unsetVersionToken(): self
    {
        $this->instance->unsetVersionToken();
        return $this;
    }

    /**
     * Initializes a new Payment object.
     */
    public function build(): Payment
    {
        return CoreHelper::clone($this->instance);
    }
}
