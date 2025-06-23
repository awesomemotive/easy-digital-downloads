<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Address;
use EDD\Vendor\Square\Models\CashPaymentDetails;
use EDD\Vendor\Square\Models\CreatePaymentRequest;
use EDD\Vendor\Square\Models\CustomerDetails;
use EDD\Vendor\Square\Models\ExternalPaymentDetails;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\OfflinePaymentDetails;

/**
 * Builder for model CreatePaymentRequest
 *
 * @see CreatePaymentRequest
 */
class CreatePaymentRequestBuilder
{
    /**
     * @var CreatePaymentRequest
     */
    private $instance;

    private function __construct(CreatePaymentRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Payment Request Builder object.
     *
     * @param string $sourceId
     * @param string $idempotencyKey
     */
    public static function init(string $sourceId, string $idempotencyKey): self
    {
        return new self(new CreatePaymentRequest($sourceId, $idempotencyKey));
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
     * Sets autocomplete field.
     *
     * @param bool|null $value
     */
    public function autocomplete(?bool $value): self
    {
        $this->instance->setAutocomplete($value);
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
     * Sets verification token field.
     *
     * @param string|null $value
     */
    public function verificationToken(?string $value): self
    {
        $this->instance->setVerificationToken($value);
        return $this;
    }

    /**
     * Sets accept partial authorization field.
     *
     * @param bool|null $value
     */
    public function acceptPartialAuthorization(?bool $value): self
    {
        $this->instance->setAcceptPartialAuthorization($value);
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
     * Sets customer details field.
     *
     * @param CustomerDetails|null $value
     */
    public function customerDetails(?CustomerDetails $value): self
    {
        $this->instance->setCustomerDetails($value);
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
     * Initializes a new Create Payment Request object.
     */
    public function build(): CreatePaymentRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
