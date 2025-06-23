<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Stores details about an external refund. Contains only non-confidential information.
 */
class DestinationDetailsExternalRefundDetails implements \JsonSerializable
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $source;

    /**
     * @var array
     */
    private $sourceId = [];

    /**
     * @param string $type
     * @param string $source
     */
    public function __construct(string $type, string $source)
    {
        $this->type = $type;
        $this->source = $source;
    }

    /**
     * Returns Type.
     * The type of external refund the seller paid to the buyer. It can be one of the
     * following:
     * - CHECK - Refunded using a physical check.
     * - BANK_TRANSFER - Refunded using external bank transfer.
     * - OTHER\_GIFT\_CARD - Refunded using a non-EDD\Vendor\Square gift card.
     * - CRYPTO - Refunded using a crypto currency.
     * - SQUARE_CASH - Refunded using EDD\Vendor\Square Cash App.
     * - SOCIAL - Refunded using peer-to-peer payment applications.
     * - EXTERNAL - A third-party application gathered this refund outside of Square.
     * - EMONEY - Refunded using an E-money provider.
     * - CARD - A credit or debit card that EDD\Vendor\Square does not support.
     * - STORED_BALANCE - Use for house accounts, store credit, and so forth.
     * - FOOD_VOUCHER - Restaurant voucher provided by employers to employees to pay for meals
     * - OTHER - A type not listed here.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets Type.
     * The type of external refund the seller paid to the buyer. It can be one of the
     * following:
     * - CHECK - Refunded using a physical check.
     * - BANK_TRANSFER - Refunded using external bank transfer.
     * - OTHER\_GIFT\_CARD - Refunded using a non-EDD\Vendor\Square gift card.
     * - CRYPTO - Refunded using a crypto currency.
     * - SQUARE_CASH - Refunded using EDD\Vendor\Square Cash App.
     * - SOCIAL - Refunded using peer-to-peer payment applications.
     * - EXTERNAL - A third-party application gathered this refund outside of Square.
     * - EMONEY - Refunded using an E-money provider.
     * - CARD - A credit or debit card that EDD\Vendor\Square does not support.
     * - STORED_BALANCE - Use for house accounts, store credit, and so forth.
     * - FOOD_VOUCHER - Restaurant voucher provided by employers to employees to pay for meals
     * - OTHER - A type not listed here.
     *
     * @required
     * @maps type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns Source.
     * A description of the external refund source. For example,
     * "Food Delivery Service".
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Sets Source.
     * A description of the external refund source. For example,
     * "Food Delivery Service".
     *
     * @required
     * @maps source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    /**
     * Returns Source Id.
     * An ID to associate the refund to its originating source.
     */
    public function getSourceId(): ?string
    {
        if (count($this->sourceId) == 0) {
            return null;
        }
        return $this->sourceId['value'];
    }

    /**
     * Sets Source Id.
     * An ID to associate the refund to its originating source.
     *
     * @maps source_id
     */
    public function setSourceId(?string $sourceId): void
    {
        $this->sourceId['value'] = $sourceId;
    }

    /**
     * Unsets Source Id.
     * An ID to associate the refund to its originating source.
     */
    public function unsetSourceId(): void
    {
        $this->sourceId = [];
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        $json['type']          = $this->type;
        $json['source']        = $this->source;
        if (!empty($this->sourceId)) {
            $json['source_id'] = $this->sourceId['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
