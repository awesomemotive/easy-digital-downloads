<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Details specific to offline payments.
 */
class OfflinePaymentDetails implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $clientCreatedAt;

    /**
     * Returns Client Created At.
     * The client-side timestamp of when the offline payment was created, in RFC 3339 format.
     */
    public function getClientCreatedAt(): ?string
    {
        return $this->clientCreatedAt;
    }

    /**
     * Sets Client Created At.
     * The client-side timestamp of when the offline payment was created, in RFC 3339 format.
     *
     * @maps client_created_at
     */
    public function setClientCreatedAt(?string $clientCreatedAt): void
    {
        $this->clientCreatedAt = $clientCreatedAt;
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
        if (isset($this->clientCreatedAt)) {
            $json['client_created_at'] = $this->clientCreatedAt;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
