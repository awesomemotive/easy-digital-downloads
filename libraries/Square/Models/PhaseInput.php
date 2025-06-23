<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents the arguments used to construct a new phase.
 */
class PhaseInput implements \JsonSerializable
{
    /**
     * @var int
     */
    private $ordinal;

    /**
     * @var array
     */
    private $orderTemplateId = [];

    /**
     * @param int $ordinal
     */
    public function __construct(int $ordinal)
    {
        $this->ordinal = $ordinal;
    }

    /**
     * Returns Ordinal.
     * index of phase in total subscription plan
     */
    public function getOrdinal(): int
    {
        return $this->ordinal;
    }

    /**
     * Sets Ordinal.
     * index of phase in total subscription plan
     *
     * @required
     * @maps ordinal
     */
    public function setOrdinal(int $ordinal): void
    {
        $this->ordinal = $ordinal;
    }

    /**
     * Returns Order Template Id.
     * id of order to be used in billing
     */
    public function getOrderTemplateId(): ?string
    {
        if (count($this->orderTemplateId) == 0) {
            return null;
        }
        return $this->orderTemplateId['value'];
    }

    /**
     * Sets Order Template Id.
     * id of order to be used in billing
     *
     * @maps order_template_id
     */
    public function setOrderTemplateId(?string $orderTemplateId): void
    {
        $this->orderTemplateId['value'] = $orderTemplateId;
    }

    /**
     * Unsets Order Template Id.
     * id of order to be used in billing
     */
    public function unsetOrderTemplateId(): void
    {
        $this->orderTemplateId = [];
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
        $json['ordinal']               = $this->ordinal;
        if (!empty($this->orderTemplateId)) {
            $json['order_template_id'] = $this->orderTemplateId['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
