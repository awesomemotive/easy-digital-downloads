<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class CashDrawerShiftEvent implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $eventType;

    /**
     * @var Money|null
     */
    private $eventMoney;

    /**
     * @var string|null
     */
    private $createdAt;

    /**
     * @var array
     */
    private $description = [];

    /**
     * @var string|null
     */
    private $teamMemberId;

    /**
     * Returns Id.
     * The unique ID of the event.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The unique ID of the event.
     *
     * @maps id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns Event Type.
     * The types of events on a CashDrawerShift.
     * Each event type represents an employee action on the actual cash drawer
     * represented by a CashDrawerShift.
     */
    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    /**
     * Sets Event Type.
     * The types of events on a CashDrawerShift.
     * Each event type represents an employee action on the actual cash drawer
     * represented by a CashDrawerShift.
     *
     * @maps event_type
     */
    public function setEventType(?string $eventType): void
    {
        $this->eventType = $eventType;
    }

    /**
     * Returns Event Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getEventMoney(): ?Money
    {
        return $this->eventMoney;
    }

    /**
     * Sets Event Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps event_money
     */
    public function setEventMoney(?Money $eventMoney): void
    {
        $this->eventMoney = $eventMoney;
    }

    /**
     * Returns Created At.
     * The event time in RFC 3339 format.
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Sets Created At.
     * The event time in RFC 3339 format.
     *
     * @maps created_at
     */
    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Returns Description.
     * An optional description of the event, entered by the employee that
     * created the event.
     */
    public function getDescription(): ?string
    {
        if (count($this->description) == 0) {
            return null;
        }
        return $this->description['value'];
    }

    /**
     * Sets Description.
     * An optional description of the event, entered by the employee that
     * created the event.
     *
     * @maps description
     */
    public function setDescription(?string $description): void
    {
        $this->description['value'] = $description;
    }

    /**
     * Unsets Description.
     * An optional description of the event, entered by the employee that
     * created the event.
     */
    public function unsetDescription(): void
    {
        $this->description = [];
    }

    /**
     * Returns Team Member Id.
     * The ID of the team member that created the event.
     */
    public function getTeamMemberId(): ?string
    {
        return $this->teamMemberId;
    }

    /**
     * Sets Team Member Id.
     * The ID of the team member that created the event.
     *
     * @maps team_member_id
     */
    public function setTeamMemberId(?string $teamMemberId): void
    {
        $this->teamMemberId = $teamMemberId;
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
        if (isset($this->id)) {
            $json['id']             = $this->id;
        }
        if (isset($this->eventType)) {
            $json['event_type']     = $this->eventType;
        }
        if (isset($this->eventMoney)) {
            $json['event_money']    = $this->eventMoney;
        }
        if (isset($this->createdAt)) {
            $json['created_at']     = $this->createdAt;
        }
        if (!empty($this->description)) {
            $json['description']    = $this->description['value'];
        }
        if (isset($this->teamMemberId)) {
            $json['team_member_id'] = $this->teamMemberId;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
