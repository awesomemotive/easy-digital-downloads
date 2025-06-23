<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class ConfirmationDecision implements \JsonSerializable
{
    /**
     * @var bool|null
     */
    private $hasAgreed;

    /**
     * Returns Has Agreed.
     * The buyer's decision to the displayed terms.
     */
    public function getHasAgreed(): ?bool
    {
        return $this->hasAgreed;
    }

    /**
     * Sets Has Agreed.
     * The buyer's decision to the displayed terms.
     *
     * @maps has_agreed
     */
    public function setHasAgreed(?bool $hasAgreed): void
    {
        $this->hasAgreed = $hasAgreed;
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
        if (isset($this->hasAgreed)) {
            $json['has_agreed'] = $this->hasAgreed;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
