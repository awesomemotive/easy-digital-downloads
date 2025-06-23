<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents a phase, which can override subscription phases as defined by plan_id
 */
class Phase implements \JsonSerializable
{
    /**
     * @var array
     */
    private $uid = [];

    /**
     * @var array
     */
    private $ordinal = [];

    /**
     * @var array
     */
    private $orderTemplateId = [];

    /**
     * @var array
     */
    private $planPhaseUid = [];

    /**
     * Returns Uid.
     * id of subscription phase
     */
    public function getUid(): ?string
    {
        if (count($this->uid) == 0) {
            return null;
        }
        return $this->uid['value'];
    }

    /**
     * Sets Uid.
     * id of subscription phase
     *
     * @maps uid
     */
    public function setUid(?string $uid): void
    {
        $this->uid['value'] = $uid;
    }

    /**
     * Unsets Uid.
     * id of subscription phase
     */
    public function unsetUid(): void
    {
        $this->uid = [];
    }

    /**
     * Returns Ordinal.
     * index of phase in total subscription plan
     */
    public function getOrdinal(): ?int
    {
        if (count($this->ordinal) == 0) {
            return null;
        }
        return $this->ordinal['value'];
    }

    /**
     * Sets Ordinal.
     * index of phase in total subscription plan
     *
     * @maps ordinal
     */
    public function setOrdinal(?int $ordinal): void
    {
        $this->ordinal['value'] = $ordinal;
    }

    /**
     * Unsets Ordinal.
     * index of phase in total subscription plan
     */
    public function unsetOrdinal(): void
    {
        $this->ordinal = [];
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
     * Returns Plan Phase Uid.
     * the uid from the plan's phase in catalog
     */
    public function getPlanPhaseUid(): ?string
    {
        if (count($this->planPhaseUid) == 0) {
            return null;
        }
        return $this->planPhaseUid['value'];
    }

    /**
     * Sets Plan Phase Uid.
     * the uid from the plan's phase in catalog
     *
     * @maps plan_phase_uid
     */
    public function setPlanPhaseUid(?string $planPhaseUid): void
    {
        $this->planPhaseUid['value'] = $planPhaseUid;
    }

    /**
     * Unsets Plan Phase Uid.
     * the uid from the plan's phase in catalog
     */
    public function unsetPlanPhaseUid(): void
    {
        $this->planPhaseUid = [];
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
        if (!empty($this->uid)) {
            $json['uid']               = $this->uid['value'];
        }
        if (!empty($this->ordinal)) {
            $json['ordinal']           = $this->ordinal['value'];
        }
        if (!empty($this->orderTemplateId)) {
            $json['order_template_id'] = $this->orderTemplateId['value'];
        }
        if (!empty($this->planPhaseUid)) {
            $json['plan_phase_uid']    = $this->planPhaseUid['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
