<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\GiftCardActivity;
use EDD\Vendor\Square\Models\GiftCardActivityActivate;
use EDD\Vendor\Square\Models\GiftCardActivityAdjustDecrement;
use EDD\Vendor\Square\Models\GiftCardActivityAdjustIncrement;
use EDD\Vendor\Square\Models\GiftCardActivityBlock;
use EDD\Vendor\Square\Models\GiftCardActivityClearBalance;
use EDD\Vendor\Square\Models\GiftCardActivityDeactivate;
use EDD\Vendor\Square\Models\GiftCardActivityImport;
use EDD\Vendor\Square\Models\GiftCardActivityImportReversal;
use EDD\Vendor\Square\Models\GiftCardActivityLoad;
use EDD\Vendor\Square\Models\GiftCardActivityRedeem;
use EDD\Vendor\Square\Models\GiftCardActivityRefund;
use EDD\Vendor\Square\Models\GiftCardActivityTransferBalanceFrom;
use EDD\Vendor\Square\Models\GiftCardActivityTransferBalanceTo;
use EDD\Vendor\Square\Models\GiftCardActivityUnblock;
use EDD\Vendor\Square\Models\GiftCardActivityUnlinkedActivityRefund;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model GiftCardActivity
 *
 * @see GiftCardActivity
 */
class GiftCardActivityBuilder
{
    /**
     * @var GiftCardActivity
     */
    private $instance;

    private function __construct(GiftCardActivity $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Gift Card Activity Builder object.
     *
     * @param string $type
     * @param string $locationId
     */
    public static function init(string $type, string $locationId): self
    {
        return new self(new GiftCardActivity($type, $locationId));
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
     * Sets gift card id field.
     *
     * @param string|null $value
     */
    public function giftCardId(?string $value): self
    {
        $this->instance->setGiftCardId($value);
        return $this;
    }

    /**
     * Unsets gift card id field.
     */
    public function unsetGiftCardId(): self
    {
        $this->instance->unsetGiftCardId();
        return $this;
    }

    /**
     * Sets gift card gan field.
     *
     * @param string|null $value
     */
    public function giftCardGan(?string $value): self
    {
        $this->instance->setGiftCardGan($value);
        return $this;
    }

    /**
     * Unsets gift card gan field.
     */
    public function unsetGiftCardGan(): self
    {
        $this->instance->unsetGiftCardGan();
        return $this;
    }

    /**
     * Sets gift card balance money field.
     *
     * @param Money|null $value
     */
    public function giftCardBalanceMoney(?Money $value): self
    {
        $this->instance->setGiftCardBalanceMoney($value);
        return $this;
    }

    /**
     * Sets load activity details field.
     *
     * @param GiftCardActivityLoad|null $value
     */
    public function loadActivityDetails(?GiftCardActivityLoad $value): self
    {
        $this->instance->setLoadActivityDetails($value);
        return $this;
    }

    /**
     * Sets activate activity details field.
     *
     * @param GiftCardActivityActivate|null $value
     */
    public function activateActivityDetails(?GiftCardActivityActivate $value): self
    {
        $this->instance->setActivateActivityDetails($value);
        return $this;
    }

    /**
     * Sets redeem activity details field.
     *
     * @param GiftCardActivityRedeem|null $value
     */
    public function redeemActivityDetails(?GiftCardActivityRedeem $value): self
    {
        $this->instance->setRedeemActivityDetails($value);
        return $this;
    }

    /**
     * Sets clear balance activity details field.
     *
     * @param GiftCardActivityClearBalance|null $value
     */
    public function clearBalanceActivityDetails(?GiftCardActivityClearBalance $value): self
    {
        $this->instance->setClearBalanceActivityDetails($value);
        return $this;
    }

    /**
     * Sets deactivate activity details field.
     *
     * @param GiftCardActivityDeactivate|null $value
     */
    public function deactivateActivityDetails(?GiftCardActivityDeactivate $value): self
    {
        $this->instance->setDeactivateActivityDetails($value);
        return $this;
    }

    /**
     * Sets adjust increment activity details field.
     *
     * @param GiftCardActivityAdjustIncrement|null $value
     */
    public function adjustIncrementActivityDetails(?GiftCardActivityAdjustIncrement $value): self
    {
        $this->instance->setAdjustIncrementActivityDetails($value);
        return $this;
    }

    /**
     * Sets adjust decrement activity details field.
     *
     * @param GiftCardActivityAdjustDecrement|null $value
     */
    public function adjustDecrementActivityDetails(?GiftCardActivityAdjustDecrement $value): self
    {
        $this->instance->setAdjustDecrementActivityDetails($value);
        return $this;
    }

    /**
     * Sets refund activity details field.
     *
     * @param GiftCardActivityRefund|null $value
     */
    public function refundActivityDetails(?GiftCardActivityRefund $value): self
    {
        $this->instance->setRefundActivityDetails($value);
        return $this;
    }

    /**
     * Sets unlinked activity refund activity details field.
     *
     * @param GiftCardActivityUnlinkedActivityRefund|null $value
     */
    public function unlinkedActivityRefundActivityDetails(?GiftCardActivityUnlinkedActivityRefund $value): self
    {
        $this->instance->setUnlinkedActivityRefundActivityDetails($value);
        return $this;
    }

    /**
     * Sets import activity details field.
     *
     * @param GiftCardActivityImport|null $value
     */
    public function importActivityDetails(?GiftCardActivityImport $value): self
    {
        $this->instance->setImportActivityDetails($value);
        return $this;
    }

    /**
     * Sets block activity details field.
     *
     * @param GiftCardActivityBlock|null $value
     */
    public function blockActivityDetails(?GiftCardActivityBlock $value): self
    {
        $this->instance->setBlockActivityDetails($value);
        return $this;
    }

    /**
     * Sets unblock activity details field.
     *
     * @param GiftCardActivityUnblock|null $value
     */
    public function unblockActivityDetails(?GiftCardActivityUnblock $value): self
    {
        $this->instance->setUnblockActivityDetails($value);
        return $this;
    }

    /**
     * Sets import reversal activity details field.
     *
     * @param GiftCardActivityImportReversal|null $value
     */
    public function importReversalActivityDetails(?GiftCardActivityImportReversal $value): self
    {
        $this->instance->setImportReversalActivityDetails($value);
        return $this;
    }

    /**
     * Sets transfer balance to activity details field.
     *
     * @param GiftCardActivityTransferBalanceTo|null $value
     */
    public function transferBalanceToActivityDetails(?GiftCardActivityTransferBalanceTo $value): self
    {
        $this->instance->setTransferBalanceToActivityDetails($value);
        return $this;
    }

    /**
     * Sets transfer balance from activity details field.
     *
     * @param GiftCardActivityTransferBalanceFrom|null $value
     */
    public function transferBalanceFromActivityDetails(?GiftCardActivityTransferBalanceFrom $value): self
    {
        $this->instance->setTransferBalanceFromActivityDetails($value);
        return $this;
    }

    /**
     * Initializes a new Gift Card Activity object.
     */
    public function build(): GiftCardActivity
    {
        return CoreHelper::clone($this->instance);
    }
}
