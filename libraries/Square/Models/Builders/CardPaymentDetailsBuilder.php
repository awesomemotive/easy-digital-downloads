<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Card;
use EDD\Vendor\Square\Models\CardPaymentDetails;
use EDD\Vendor\Square\Models\CardPaymentTimeline;
use EDD\Vendor\Square\Models\DeviceDetails;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model CardPaymentDetails
 *
 * @see CardPaymentDetails
 */
class CardPaymentDetailsBuilder
{
    /**
     * @var CardPaymentDetails
     */
    private $instance;

    private function __construct(CardPaymentDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Card Payment Details Builder object.
     */
    public static function init(): self
    {
        return new self(new CardPaymentDetails());
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
     * Unsets status field.
     */
    public function unsetStatus(): self
    {
        $this->instance->unsetStatus();
        return $this;
    }

    /**
     * Sets card field.
     *
     * @param Card|null $value
     */
    public function card(?Card $value): self
    {
        $this->instance->setCard($value);
        return $this;
    }

    /**
     * Sets entry method field.
     *
     * @param string|null $value
     */
    public function entryMethod(?string $value): self
    {
        $this->instance->setEntryMethod($value);
        return $this;
    }

    /**
     * Unsets entry method field.
     */
    public function unsetEntryMethod(): self
    {
        $this->instance->unsetEntryMethod();
        return $this;
    }

    /**
     * Sets cvv status field.
     *
     * @param string|null $value
     */
    public function cvvStatus(?string $value): self
    {
        $this->instance->setCvvStatus($value);
        return $this;
    }

    /**
     * Unsets cvv status field.
     */
    public function unsetCvvStatus(): self
    {
        $this->instance->unsetCvvStatus();
        return $this;
    }

    /**
     * Sets avs status field.
     *
     * @param string|null $value
     */
    public function avsStatus(?string $value): self
    {
        $this->instance->setAvsStatus($value);
        return $this;
    }

    /**
     * Unsets avs status field.
     */
    public function unsetAvsStatus(): self
    {
        $this->instance->unsetAvsStatus();
        return $this;
    }

    /**
     * Sets auth result code field.
     *
     * @param string|null $value
     */
    public function authResultCode(?string $value): self
    {
        $this->instance->setAuthResultCode($value);
        return $this;
    }

    /**
     * Unsets auth result code field.
     */
    public function unsetAuthResultCode(): self
    {
        $this->instance->unsetAuthResultCode();
        return $this;
    }

    /**
     * Sets application identifier field.
     *
     * @param string|null $value
     */
    public function applicationIdentifier(?string $value): self
    {
        $this->instance->setApplicationIdentifier($value);
        return $this;
    }

    /**
     * Unsets application identifier field.
     */
    public function unsetApplicationIdentifier(): self
    {
        $this->instance->unsetApplicationIdentifier();
        return $this;
    }

    /**
     * Sets application name field.
     *
     * @param string|null $value
     */
    public function applicationName(?string $value): self
    {
        $this->instance->setApplicationName($value);
        return $this;
    }

    /**
     * Unsets application name field.
     */
    public function unsetApplicationName(): self
    {
        $this->instance->unsetApplicationName();
        return $this;
    }

    /**
     * Sets application cryptogram field.
     *
     * @param string|null $value
     */
    public function applicationCryptogram(?string $value): self
    {
        $this->instance->setApplicationCryptogram($value);
        return $this;
    }

    /**
     * Unsets application cryptogram field.
     */
    public function unsetApplicationCryptogram(): self
    {
        $this->instance->unsetApplicationCryptogram();
        return $this;
    }

    /**
     * Sets verification method field.
     *
     * @param string|null $value
     */
    public function verificationMethod(?string $value): self
    {
        $this->instance->setVerificationMethod($value);
        return $this;
    }

    /**
     * Unsets verification method field.
     */
    public function unsetVerificationMethod(): self
    {
        $this->instance->unsetVerificationMethod();
        return $this;
    }

    /**
     * Sets verification results field.
     *
     * @param string|null $value
     */
    public function verificationResults(?string $value): self
    {
        $this->instance->setVerificationResults($value);
        return $this;
    }

    /**
     * Unsets verification results field.
     */
    public function unsetVerificationResults(): self
    {
        $this->instance->unsetVerificationResults();
        return $this;
    }

    /**
     * Sets statement description field.
     *
     * @param string|null $value
     */
    public function statementDescription(?string $value): self
    {
        $this->instance->setStatementDescription($value);
        return $this;
    }

    /**
     * Unsets statement description field.
     */
    public function unsetStatementDescription(): self
    {
        $this->instance->unsetStatementDescription();
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
     * Sets card payment timeline field.
     *
     * @param CardPaymentTimeline|null $value
     */
    public function cardPaymentTimeline(?CardPaymentTimeline $value): self
    {
        $this->instance->setCardPaymentTimeline($value);
        return $this;
    }

    /**
     * Sets refund requires card presence field.
     *
     * @param bool|null $value
     */
    public function refundRequiresCardPresence(?bool $value): self
    {
        $this->instance->setRefundRequiresCardPresence($value);
        return $this;
    }

    /**
     * Unsets refund requires card presence field.
     */
    public function unsetRefundRequiresCardPresence(): self
    {
        $this->instance->unsetRefundRequiresCardPresence();
        return $this;
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Unsets errors field.
     */
    public function unsetErrors(): self
    {
        $this->instance->unsetErrors();
        return $this;
    }

    /**
     * Initializes a new Card Payment Details object.
     */
    public function build(): CardPaymentDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
