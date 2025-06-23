<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Address;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\V1Money;
use EDD\Vendor\Square\Models\V1Order;
use EDD\Vendor\Square\Models\V1OrderHistoryEntry;
use EDD\Vendor\Square\Models\V1Tender;

/**
 * Builder for model V1Order
 *
 * @see V1Order
 */
class V1OrderBuilder
{
    /**
     * @var V1Order
     */
    private $instance;

    private function __construct(V1Order $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new V1 Order Builder object.
     */
    public static function init(): self
    {
        return new self(new V1Order());
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
     * Sets buyer email field.
     *
     * @param string|null $value
     */
    public function buyerEmail(?string $value): self
    {
        $this->instance->setBuyerEmail($value);
        return $this;
    }

    /**
     * Unsets buyer email field.
     */
    public function unsetBuyerEmail(): self
    {
        $this->instance->unsetBuyerEmail();
        return $this;
    }

    /**
     * Sets recipient name field.
     *
     * @param string|null $value
     */
    public function recipientName(?string $value): self
    {
        $this->instance->setRecipientName($value);
        return $this;
    }

    /**
     * Unsets recipient name field.
     */
    public function unsetRecipientName(): self
    {
        $this->instance->unsetRecipientName();
        return $this;
    }

    /**
     * Sets recipient phone number field.
     *
     * @param string|null $value
     */
    public function recipientPhoneNumber(?string $value): self
    {
        $this->instance->setRecipientPhoneNumber($value);
        return $this;
    }

    /**
     * Unsets recipient phone number field.
     */
    public function unsetRecipientPhoneNumber(): self
    {
        $this->instance->unsetRecipientPhoneNumber();
        return $this;
    }

    /**
     * Sets state field.
     *
     * @param string|null $value
     */
    public function state(?string $value): self
    {
        $this->instance->setState($value);
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
     * Sets subtotal money field.
     *
     * @param V1Money|null $value
     */
    public function subtotalMoney(?V1Money $value): self
    {
        $this->instance->setSubtotalMoney($value);
        return $this;
    }

    /**
     * Sets total shipping money field.
     *
     * @param V1Money|null $value
     */
    public function totalShippingMoney(?V1Money $value): self
    {
        $this->instance->setTotalShippingMoney($value);
        return $this;
    }

    /**
     * Sets total tax money field.
     *
     * @param V1Money|null $value
     */
    public function totalTaxMoney(?V1Money $value): self
    {
        $this->instance->setTotalTaxMoney($value);
        return $this;
    }

    /**
     * Sets total price money field.
     *
     * @param V1Money|null $value
     */
    public function totalPriceMoney(?V1Money $value): self
    {
        $this->instance->setTotalPriceMoney($value);
        return $this;
    }

    /**
     * Sets total discount money field.
     *
     * @param V1Money|null $value
     */
    public function totalDiscountMoney(?V1Money $value): self
    {
        $this->instance->setTotalDiscountMoney($value);
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
     * Sets expires at field.
     *
     * @param string|null $value
     */
    public function expiresAt(?string $value): self
    {
        $this->instance->setExpiresAt($value);
        return $this;
    }

    /**
     * Unsets expires at field.
     */
    public function unsetExpiresAt(): self
    {
        $this->instance->unsetExpiresAt();
        return $this;
    }

    /**
     * Sets payment id field.
     *
     * @param string|null $value
     */
    public function paymentId(?string $value): self
    {
        $this->instance->setPaymentId($value);
        return $this;
    }

    /**
     * Unsets payment id field.
     */
    public function unsetPaymentId(): self
    {
        $this->instance->unsetPaymentId();
        return $this;
    }

    /**
     * Sets buyer note field.
     *
     * @param string|null $value
     */
    public function buyerNote(?string $value): self
    {
        $this->instance->setBuyerNote($value);
        return $this;
    }

    /**
     * Unsets buyer note field.
     */
    public function unsetBuyerNote(): self
    {
        $this->instance->unsetBuyerNote();
        return $this;
    }

    /**
     * Sets completed note field.
     *
     * @param string|null $value
     */
    public function completedNote(?string $value): self
    {
        $this->instance->setCompletedNote($value);
        return $this;
    }

    /**
     * Unsets completed note field.
     */
    public function unsetCompletedNote(): self
    {
        $this->instance->unsetCompletedNote();
        return $this;
    }

    /**
     * Sets refunded note field.
     *
     * @param string|null $value
     */
    public function refundedNote(?string $value): self
    {
        $this->instance->setRefundedNote($value);
        return $this;
    }

    /**
     * Unsets refunded note field.
     */
    public function unsetRefundedNote(): self
    {
        $this->instance->unsetRefundedNote();
        return $this;
    }

    /**
     * Sets canceled note field.
     *
     * @param string|null $value
     */
    public function canceledNote(?string $value): self
    {
        $this->instance->setCanceledNote($value);
        return $this;
    }

    /**
     * Unsets canceled note field.
     */
    public function unsetCanceledNote(): self
    {
        $this->instance->unsetCanceledNote();
        return $this;
    }

    /**
     * Sets tender field.
     *
     * @param V1Tender|null $value
     */
    public function tender(?V1Tender $value): self
    {
        $this->instance->setTender($value);
        return $this;
    }

    /**
     * Sets order history field.
     *
     * @param V1OrderHistoryEntry[]|null $value
     */
    public function orderHistory(?array $value): self
    {
        $this->instance->setOrderHistory($value);
        return $this;
    }

    /**
     * Unsets order history field.
     */
    public function unsetOrderHistory(): self
    {
        $this->instance->unsetOrderHistory();
        return $this;
    }

    /**
     * Sets promo code field.
     *
     * @param string|null $value
     */
    public function promoCode(?string $value): self
    {
        $this->instance->setPromoCode($value);
        return $this;
    }

    /**
     * Unsets promo code field.
     */
    public function unsetPromoCode(): self
    {
        $this->instance->unsetPromoCode();
        return $this;
    }

    /**
     * Sets btc receive address field.
     *
     * @param string|null $value
     */
    public function btcReceiveAddress(?string $value): self
    {
        $this->instance->setBtcReceiveAddress($value);
        return $this;
    }

    /**
     * Unsets btc receive address field.
     */
    public function unsetBtcReceiveAddress(): self
    {
        $this->instance->unsetBtcReceiveAddress();
        return $this;
    }

    /**
     * Sets btc price satoshi field.
     *
     * @param float|null $value
     */
    public function btcPriceSatoshi(?float $value): self
    {
        $this->instance->setBtcPriceSatoshi($value);
        return $this;
    }

    /**
     * Unsets btc price satoshi field.
     */
    public function unsetBtcPriceSatoshi(): self
    {
        $this->instance->unsetBtcPriceSatoshi();
        return $this;
    }

    /**
     * Initializes a new V1 Order object.
     */
    public function build(): V1Order
    {
        return CoreHelper::clone($this->instance);
    }
}
