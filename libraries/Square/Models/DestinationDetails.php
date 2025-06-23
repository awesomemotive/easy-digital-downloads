<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Details about a refund's destination.
 */
class DestinationDetails implements \JsonSerializable
{
    /**
     * @var DestinationDetailsCardRefundDetails|null
     */
    private $cardDetails;

    /**
     * @var DestinationDetailsCashRefundDetails|null
     */
    private $cashDetails;

    /**
     * @var DestinationDetailsExternalRefundDetails|null
     */
    private $externalDetails;

    /**
     * Returns Card Details.
     */
    public function getCardDetails(): ?DestinationDetailsCardRefundDetails
    {
        return $this->cardDetails;
    }

    /**
     * Sets Card Details.
     *
     * @maps card_details
     */
    public function setCardDetails(?DestinationDetailsCardRefundDetails $cardDetails): void
    {
        $this->cardDetails = $cardDetails;
    }

    /**
     * Returns Cash Details.
     * Stores details about a cash refund. Contains only non-confidential information.
     */
    public function getCashDetails(): ?DestinationDetailsCashRefundDetails
    {
        return $this->cashDetails;
    }

    /**
     * Sets Cash Details.
     * Stores details about a cash refund. Contains only non-confidential information.
     *
     * @maps cash_details
     */
    public function setCashDetails(?DestinationDetailsCashRefundDetails $cashDetails): void
    {
        $this->cashDetails = $cashDetails;
    }

    /**
     * Returns External Details.
     * Stores details about an external refund. Contains only non-confidential information.
     */
    public function getExternalDetails(): ?DestinationDetailsExternalRefundDetails
    {
        return $this->externalDetails;
    }

    /**
     * Sets External Details.
     * Stores details about an external refund. Contains only non-confidential information.
     *
     * @maps external_details
     */
    public function setExternalDetails(?DestinationDetailsExternalRefundDetails $externalDetails): void
    {
        $this->externalDetails = $externalDetails;
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
        if (isset($this->cardDetails)) {
            $json['card_details']     = $this->cardDetails;
        }
        if (isset($this->cashDetails)) {
            $json['cash_details']     = $this->cashDetails;
        }
        if (isset($this->externalDetails)) {
            $json['external_details'] = $this->externalDetails;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
