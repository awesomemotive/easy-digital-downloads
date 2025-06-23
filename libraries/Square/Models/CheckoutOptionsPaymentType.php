<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

class CheckoutOptionsPaymentType
{
    /**
     * Accept credit card or debit card payments via tap, dip or swipe.
     */
    public const CARD_PRESENT = 'CARD_PRESENT';

    /**
     * Launches the manual credit or debit card entry screen for the buyer to complete.
     */
    public const MANUAL_CARD_ENTRY = 'MANUAL_CARD_ENTRY';

    /**
     * Launches the iD checkout screen for the buyer to complete.
     */
    public const FELICA_ID = 'FELICA_ID';

    /**
     * Launches the QUICPay checkout screen for the buyer to complete.
     */
    public const FELICA_QUICPAY = 'FELICA_QUICPAY';

    /**
     * Launches the Transportation Group checkout screen for the buyer to complete.
     */
    public const FELICA_TRANSPORTATION_GROUP = 'FELICA_TRANSPORTATION_GROUP';

    /**
     * Launches a checkout screen for the buyer on the EDD\Vendor\Square Terminal that
     * allows them to select a specific FeliCa brand or select the check balance screen.
     */
    public const FELICA_ALL = 'FELICA_ALL';

    /**
     * Replaced by `QR_CODE`.
     */
    public const PAYPAY = 'PAYPAY';

    /**
     * Launches Square's QR Code checkout screen for the buyer to complete.
     * Displays a single code that supports all digital wallets connected to the target
     * Seller location (e.g. PayPay)
     */
    public const QR_CODE = 'QR_CODE';
}
