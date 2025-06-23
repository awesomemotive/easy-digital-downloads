<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Describes the type of this unit and indicates which field contains the unit information. This is an
 * ‘open’ enum.
 */
class TerminalActionActionType
{
    /**
     * The action represents a request to display a QR code. Details are contained in
     * the `qr_code_options` object.
     */
    public const QR_CODE = 'QR_CODE';

    /**
     * The action represents a request to check if the specific device is
     * online or currently active with the merchant in question. Does not require an action options value.
     */
    public const PING = 'PING';

    /**
     * Represents a request to save a card for future card-on-file use. Details are contained
     * in the `save_card_options` object.
     */
    public const SAVE_CARD = 'SAVE_CARD';

    /**
     * The action represents a request to capture a buyer's signature. Details are contained
     * in the `signature_options` object.
     */
    public const SIGNATURE = 'SIGNATURE';

    /**
     * The action represents a request to collect a buyer's confirmation decision to the
     * displayed terms. Details are contained in the `confirmation_options` object.
     */
    public const CONFIRMATION = 'CONFIRMATION';

    /**
     * The action represents a request to display the receipt screen options. Details are
     * contained in the `receipt_options` object.
     */
    public const RECEIPT = 'RECEIPT';

    /**
     * The action represents a request to collect a buyer's text data. Details
     * are contained in the `data_collection_options` object.
     */
    public const DATA_COLLECTION = 'DATA_COLLECTION';

    /**
     * The action represents a request to allow the buyer to select from provided options.
     * Details are contained in the `select_options` object.
     */
    public const SELECT = 'SELECT';
}
