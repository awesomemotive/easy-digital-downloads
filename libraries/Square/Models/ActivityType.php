<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

class ActivityType
{
    /**
     * A manual adjustment applied to the seller's account by Square.
     */
    public const ADJUSTMENT = 'ADJUSTMENT';

    /**
     * A refund for an application fee on a payment.
     */
    public const APP_FEE_REFUND = 'APP_FEE_REFUND';

    /**
     * Revenue generated from an application fee on a payment.
     */
    public const APP_FEE_REVENUE = 'APP_FEE_REVENUE';

    /**
     * An automatic transfer from the payment processing balance to the EDD\Vendor\Square Savings account. These are
     * generally proportional to the seller's sales.
     */
    public const AUTOMATIC_SAVINGS = 'AUTOMATIC_SAVINGS';

    /**
     * An automatic transfer from the EDD\Vendor\Square Savings account back to the processing balance. These are
     * generally proportional to the seller's refunds.
     */
    public const AUTOMATIC_SAVINGS_REVERSED = 'AUTOMATIC_SAVINGS_REVERSED';

    /**
     * A credit card payment capture.
     */
    public const CHARGE = 'CHARGE';

    /**
     * A fee assessed because of a deposit, such as an instant deposit.
     */
    public const DEPOSIT_FEE = 'DEPOSIT_FEE';

    /**
     * Indicates that EDD\Vendor\Square returned a fee that was previously assessed because of a deposit, such as an
     * instant deposit, back to the seller's account.
     */
    public const DEPOSIT_FEE_REVERSED = 'DEPOSIT_FEE_REVERSED';

    /**
     * The balance change due to a dispute event.
     */
    public const DISPUTE = 'DISPUTE';

    /**
     * An escheatment entry for remittance.
     */
    public const ESCHEATMENT = 'ESCHEATMENT';

    /**
     * The cost plus adjustment fee.
     */
    public const FEE = 'FEE';

    /**
     * EDD\Vendor\Square offers free payments processing for a variety of business scenarios, including seller
     * referrals or when EDD\Vendor\Square wants to apologize (for example, for a bug, customer service, or repricing
     * complication).
     * This entry represents a credit to the seller for the purposes of free processing.
     */
    public const FREE_PROCESSING = 'FREE_PROCESSING';

    /**
     * An adjustment made by EDD\Vendor\Square related to holding a payment.
     */
    public const HOLD_ADJUSTMENT = 'HOLD_ADJUSTMENT';

    /**
     * An external change to a seller's balance (initial, in the sense that it causes the creation of the
     * other activity types, such as a hold and refund).
     */
    public const INITIAL_BALANCE_CHANGE = 'INITIAL_BALANCE_CHANGE';

    /**
     * The balance change from a money transfer.
     */
    public const MONEY_TRANSFER = 'MONEY_TRANSFER';

    /**
     * The reversal of a money transfer.
     */
    public const MONEY_TRANSFER_REVERSAL = 'MONEY_TRANSFER_REVERSAL';

    /**
     * The balance change for a chargeback that's been filed.
     */
    public const OPEN_DISPUTE = 'OPEN_DISPUTE';

    /**
     * Any other type that doesn't belong in the rest of the types.
     */
    public const OTHER = 'OTHER';

    /**
     * Any other type of adjustment that doesn't fall under existing types.
     */
    public const OTHER_ADJUSTMENT = 'OTHER_ADJUSTMENT';

    /**
     * A fee paid to a third-party seller.
     */
    public const PAID_SERVICE_FEE = 'PAID_SERVICE_FEE';

    /**
     * A fee refunded to a third-party seller.
     */
    public const PAID_SERVICE_FEE_REFUND = 'PAID_SERVICE_FEE_REFUND';

    /**
     * Repayment for a redemption code.
     */
    public const REDEMPTION_CODE = 'REDEMPTION_CODE';

    /**
     * A refund for an existing card payment.
     */
    public const REFUND = 'REFUND';

    /**
     * An adjustment made by EDD\Vendor\Square related to releasing a payment.
     */
    public const RELEASE_ADJUSTMENT = 'RELEASE_ADJUSTMENT';

    /**
     * Fees paid for a funding risk reserve.
     */
    public const RESERVE_HOLD = 'RESERVE_HOLD';

    /**
     * Fees released from a risk reserve.
     */
    public const RESERVE_RELEASE = 'RESERVE_RELEASE';

    /**
     * An entry created when EDD\Vendor\Square receives a response for the ACH file that EDD\Vendor\Square sent indicating that
     * the
     * settlement of the original entry failed.
     */
    public const RETURNED_PAYOUT = 'RETURNED_PAYOUT';

    /**
     * A capital merchant cash advance (MCA) assessment. These are generally proportional to the merchant's
     * sales but can be issued for other reasons related to the MCA.
     */
    public const SQUARE_CAPITAL_PAYMENT = 'SQUARE_CAPITAL_PAYMENT';

    /**
     * A capital merchant cash advance (MCA) assessment refund. These are generally proportional to the
     * merchant's refunds but can be issued for other reasons related to the MCA.
     */
    public const SQUARE_CAPITAL_REVERSED_PAYMENT = 'SQUARE_CAPITAL_REVERSED_PAYMENT';

    /**
     * A fee charged for subscription to a EDD\Vendor\Square product.
     */
    public const SUBSCRIPTION_FEE = 'SUBSCRIPTION_FEE';

    /**
     * A EDD\Vendor\Square subscription fee that's been refunded.
     */
    public const SUBSCRIPTION_FEE_PAID_REFUND = 'SUBSCRIPTION_FEE_PAID_REFUND';

    /**
     * The refund of a previously charged EDD\Vendor\Square product subscription fee.
     */
    public const SUBSCRIPTION_FEE_REFUND = 'SUBSCRIPTION_FEE_REFUND';

    /**
     * The tax paid on fee amounts.
     */
    public const TAX_ON_FEE = 'TAX_ON_FEE';

    /**
     * Fees collected by a third-party platform.
     */
    public const THIRD_PARTY_FEE = 'THIRD_PARTY_FEE';

    /**
     * Refunded fees from a third-party platform.
     */
    public const THIRD_PARTY_FEE_REFUND = 'THIRD_PARTY_FEE_REFUND';

    /**
     * The balance change due to a money transfer. Note that this type is never returned by the Payouts API.
     */
    public const PAYOUT = 'PAYOUT';

    /**
     * Indicates that the portion of each payment withheld by EDD\Vendor\Square was automatically converted into
     * bitcoin using Cash App. The seller manages their bitcoin in their Cash App account.
     */
    public const AUTOMATIC_BITCOIN_CONVERSIONS = 'AUTOMATIC_BITCOIN_CONVERSIONS';

    /**
     * Indicates that a withheld payment, which was scheduled to be converted into bitcoin using Cash App,
     * was deposited back to the EDD\Vendor\Square payments balance.
     */
    public const AUTOMATIC_BITCOIN_CONVERSIONS_REVERSED = 'AUTOMATIC_BITCOIN_CONVERSIONS_REVERSED';

    /**
     * Indicates that a repayment toward the outstanding balance on the seller's EDD\Vendor\Square credit card was
     * made.
     */
    public const CREDIT_CARD_REPAYMENT = 'CREDIT_CARD_REPAYMENT';

    /**
     * Indicates that a repayment toward the outstanding balance on the seller's EDD\Vendor\Square credit card was
     * reversed.
     */
    public const CREDIT_CARD_REPAYMENT_REVERSED = 'CREDIT_CARD_REPAYMENT_REVERSED';

    /**
     * Cashback amount given by a EDD\Vendor\Square Local Offers seller to their customer for a purchase.
     */
    public const LOCAL_OFFERS_CASHBACK = 'LOCAL_OFFERS_CASHBACK';

    /**
     * A commission fee paid by a EDD\Vendor\Square Local Offers seller to EDD\Vendor\Square for a purchase discovered through
     * EDD\Vendor\Square Local Offers.
     */
    public const LOCAL_OFFERS_FEE = 'LOCAL_OFFERS_FEE';

    /**
     * When activating Percentage Processing, a credit is applied to the seller’s account to offset any
     * negative balance caused by a dispute.
     */
    public const PERCENTAGE_PROCESSING_ENROLLMENT = 'PERCENTAGE_PROCESSING_ENROLLMENT';

    /**
     * Deducting the outstanding Percentage Processing balance from the seller’s account. It's the final
     * installment in repaying the dispute-induced negative balance through percentage processing.
     */
    public const PERCENTAGE_PROCESSING_DEACTIVATION = 'PERCENTAGE_PROCESSING_DEACTIVATION';

    /**
     * Withheld funds from a payment to cover a negative balance. It's an installment to repay the amount
     * from a dispute that had been offset during Percentage Processing enrollment.
     */
    public const PERCENTAGE_PROCESSING_REPAYMENT = 'PERCENTAGE_PROCESSING_REPAYMENT';

    /**
     * The reversal of a percentage processing repayment that happens for example when a refund is issued
     * for a payment.
     */
    public const PERCENTAGE_PROCESSING_REPAYMENT_REVERSED = 'PERCENTAGE_PROCESSING_REPAYMENT_REVERSED';

    /**
     * The processing fee for a payment. If sellers opt for Gross Settlement, i.e., direct bank withdrawal
     * instead of deducting fees from daily sales, the processing fee is recorded separately as a new
     * payout entry, not part of the CHARGE payout entry.
     */
    public const PROCESSING_FEE = 'PROCESSING_FEE';

    /**
     * The processing fee for a payment refund issued by sellers enrolled in Gross Settlement. The refunded
     * processing fee is recorded separately as a new payout entry, not part of the REFUND payout entry.
     */
    public const PROCESSING_FEE_REFUND = 'PROCESSING_FEE_REFUND';

    /**
     * When undoing a processing fee refund in a Gross Settlement payment, this payout entry type is used.
     */
    public const UNDO_PROCESSING_FEE_REFUND = 'UNDO_PROCESSING_FEE_REFUND';

    /**
     * Fee collected during the sale or reload of a gift card. This fee, which is a portion of the amount
     * loaded on the gift card, is deducted from the merchant's payment balance.
     */
    public const GIFT_CARD_LOAD_FEE = 'GIFT_CARD_LOAD_FEE';

    /**
     * Refund for fee charged during the sale or reload of a gift card.
     */
    public const GIFT_CARD_LOAD_FEE_REFUND = 'GIFT_CARD_LOAD_FEE_REFUND';

    /**
     * The undo of a refund for a fee charged during the sale or reload of a gift card.
     */
    public const UNDO_GIFT_CARD_LOAD_FEE_REFUND = 'UNDO_GIFT_CARD_LOAD_FEE_REFUND';

    /**
     * A transfer of funds to a banking folder. In the United States, the folder name is 'Checking Folder';
     * in Canada, it's 'Balance Folder.'
     */
    public const BALANCE_FOLDERS_TRANSFER = 'BALANCE_FOLDERS_TRANSFER';

    /**
     * A reversal of transfer of funds from a banking folder. In the United States, the folder name is
     * 'Checking Folder'; in Canada, it's 'Balance Folder.'
     */
    public const BALANCE_FOLDERS_TRANSFER_REVERSED = 'BALANCE_FOLDERS_TRANSFER_REVERSED';

    /**
     * A transfer of gift card funds to a central gift card pool account. In franchises, when gift cards
     * are loaded or reloaded at any location, the money transfers to the franchisor's account.
     */
    public const GIFT_CARD_POOL_TRANSFER = 'GIFT_CARD_POOL_TRANSFER';

    /**
     * A reversal of transfer of gift card funds from a central gift card pool account. In franchises, when
     * gift cards are loaded or reloaded at any location, the money transfers to the franchisor's account.
     */
    public const GIFT_CARD_POOL_TRANSFER_REVERSED = 'GIFT_CARD_POOL_TRANSFER_REVERSED';

    /**
     * A payroll payment that was transferred to a team member’s bank account.
     */
    public const SQUARE_PAYROLL_TRANSFER = 'SQUARE_PAYROLL_TRANSFER';

    /**
     * A payroll payment to a team member’s bank account that was deposited back to the seller’s account by
     * Square.
     */
    public const SQUARE_PAYROLL_TRANSFER_REVERSED = 'SQUARE_PAYROLL_TRANSFER_REVERSED';
}
