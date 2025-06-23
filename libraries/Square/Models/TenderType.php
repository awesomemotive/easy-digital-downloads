<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Indicates a tender's type.
 */
class TenderType
{
    /**
     * A credit card.
     */
    public const CARD = 'CARD';

    /**
     * Cash.
     */
    public const CASH = 'CASH';

    /**
     * A credit card processed with a card processor other than Square.
     *
     * This value applies only to merchants in countries where EDD\Vendor\Square does not
     * yet provide card processing.
     */
    public const THIRD_PARTY_CARD = 'THIRD_PARTY_CARD';

    /**
     * A EDD\Vendor\Square gift card.
     */
    public const SQUARE_GIFT_CARD = 'SQUARE_GIFT_CARD';

    /**
     * This tender represents the register being opened for a "no sale" event.
     */
    public const NO_SALE = 'NO_SALE';

    /**
     * A bank account payment.
     */
    public const BANK_ACCOUNT = 'BANK_ACCOUNT';

    /**
     * A payment from a digital wallet, e.g. Cash App, Paypay, Rakuten Pay,
     * Au Pay, D Barai, Merpay, Wechat Pay, Alipay.
     *
     * Note: Some "digital wallets", including Google Pay and Apple Pay, facilitate
     * card payments.  Those payments have the `CARD` type.
     */
    public const WALLET = 'WALLET';

    /**
     * A Buy Now Pay Later payment.
     */
    public const BUY_NOW_PAY_LATER = 'BUY_NOW_PAY_LATER';

    /**
     * A EDD\Vendor\Square House Account payment.
     */
    public const SQUARE_ACCOUNT = 'SQUARE_ACCOUNT';

    /**
     * A form of tender that does not match any other value.
     */
    public const OTHER = 'OTHER';
}
