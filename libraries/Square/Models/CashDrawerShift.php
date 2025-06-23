<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * This model gives the details of a cash drawer shift.
 * The cash_payment_money, cash_refund_money, cash_paid_in_money,
 * and cash_paid_out_money fields are all computed by summing their respective
 * event types.
 */
class CashDrawerShift implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $state;

    /**
     * @var array
     */
    private $openedAt = [];

    /**
     * @var array
     */
    private $endedAt = [];

    /**
     * @var array
     */
    private $closedAt = [];

    /**
     * @var array
     */
    private $description = [];

    /**
     * @var Money|null
     */
    private $openedCashMoney;

    /**
     * @var Money|null
     */
    private $cashPaymentMoney;

    /**
     * @var Money|null
     */
    private $cashRefundsMoney;

    /**
     * @var Money|null
     */
    private $cashPaidInMoney;

    /**
     * @var Money|null
     */
    private $cashPaidOutMoney;

    /**
     * @var Money|null
     */
    private $expectedCashMoney;

    /**
     * @var Money|null
     */
    private $closedCashMoney;

    /**
     * @var CashDrawerDevice|null
     */
    private $device;

    /**
     * @var string|null
     */
    private $createdAt;

    /**
     * @var string|null
     */
    private $updatedAt;

    /**
     * @var string|null
     */
    private $locationId;

    /**
     * @var string[]|null
     */
    private $teamMemberIds;

    /**
     * @var string|null
     */
    private $openingTeamMemberId;

    /**
     * @var string|null
     */
    private $endingTeamMemberId;

    /**
     * @var string|null
     */
    private $closingTeamMemberId;

    /**
     * Returns Id.
     * The shift unique ID.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The shift unique ID.
     *
     * @maps id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns State.
     * The current state of a cash drawer shift.
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * Sets State.
     * The current state of a cash drawer shift.
     *
     * @maps state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    /**
     * Returns Opened At.
     * The time when the shift began, in ISO 8601 format.
     */
    public function getOpenedAt(): ?string
    {
        if (count($this->openedAt) == 0) {
            return null;
        }
        return $this->openedAt['value'];
    }

    /**
     * Sets Opened At.
     * The time when the shift began, in ISO 8601 format.
     *
     * @maps opened_at
     */
    public function setOpenedAt(?string $openedAt): void
    {
        $this->openedAt['value'] = $openedAt;
    }

    /**
     * Unsets Opened At.
     * The time when the shift began, in ISO 8601 format.
     */
    public function unsetOpenedAt(): void
    {
        $this->openedAt = [];
    }

    /**
     * Returns Ended At.
     * The time when the shift ended, in ISO 8601 format.
     */
    public function getEndedAt(): ?string
    {
        if (count($this->endedAt) == 0) {
            return null;
        }
        return $this->endedAt['value'];
    }

    /**
     * Sets Ended At.
     * The time when the shift ended, in ISO 8601 format.
     *
     * @maps ended_at
     */
    public function setEndedAt(?string $endedAt): void
    {
        $this->endedAt['value'] = $endedAt;
    }

    /**
     * Unsets Ended At.
     * The time when the shift ended, in ISO 8601 format.
     */
    public function unsetEndedAt(): void
    {
        $this->endedAt = [];
    }

    /**
     * Returns Closed At.
     * The time when the shift was closed, in ISO 8601 format.
     */
    public function getClosedAt(): ?string
    {
        if (count($this->closedAt) == 0) {
            return null;
        }
        return $this->closedAt['value'];
    }

    /**
     * Sets Closed At.
     * The time when the shift was closed, in ISO 8601 format.
     *
     * @maps closed_at
     */
    public function setClosedAt(?string $closedAt): void
    {
        $this->closedAt['value'] = $closedAt;
    }

    /**
     * Unsets Closed At.
     * The time when the shift was closed, in ISO 8601 format.
     */
    public function unsetClosedAt(): void
    {
        $this->closedAt = [];
    }

    /**
     * Returns Description.
     * The free-form text description of a cash drawer by an employee.
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
     * The free-form text description of a cash drawer by an employee.
     *
     * @maps description
     */
    public function setDescription(?string $description): void
    {
        $this->description['value'] = $description;
    }

    /**
     * Unsets Description.
     * The free-form text description of a cash drawer by an employee.
     */
    public function unsetDescription(): void
    {
        $this->description = [];
    }

    /**
     * Returns Opened Cash Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getOpenedCashMoney(): ?Money
    {
        return $this->openedCashMoney;
    }

    /**
     * Sets Opened Cash Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps opened_cash_money
     */
    public function setOpenedCashMoney(?Money $openedCashMoney): void
    {
        $this->openedCashMoney = $openedCashMoney;
    }

    /**
     * Returns Cash Payment Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getCashPaymentMoney(): ?Money
    {
        return $this->cashPaymentMoney;
    }

    /**
     * Sets Cash Payment Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps cash_payment_money
     */
    public function setCashPaymentMoney(?Money $cashPaymentMoney): void
    {
        $this->cashPaymentMoney = $cashPaymentMoney;
    }

    /**
     * Returns Cash Refunds Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getCashRefundsMoney(): ?Money
    {
        return $this->cashRefundsMoney;
    }

    /**
     * Sets Cash Refunds Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps cash_refunds_money
     */
    public function setCashRefundsMoney(?Money $cashRefundsMoney): void
    {
        $this->cashRefundsMoney = $cashRefundsMoney;
    }

    /**
     * Returns Cash Paid in Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getCashPaidInMoney(): ?Money
    {
        return $this->cashPaidInMoney;
    }

    /**
     * Sets Cash Paid in Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps cash_paid_in_money
     */
    public function setCashPaidInMoney(?Money $cashPaidInMoney): void
    {
        $this->cashPaidInMoney = $cashPaidInMoney;
    }

    /**
     * Returns Cash Paid Out Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getCashPaidOutMoney(): ?Money
    {
        return $this->cashPaidOutMoney;
    }

    /**
     * Sets Cash Paid Out Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps cash_paid_out_money
     */
    public function setCashPaidOutMoney(?Money $cashPaidOutMoney): void
    {
        $this->cashPaidOutMoney = $cashPaidOutMoney;
    }

    /**
     * Returns Expected Cash Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getExpectedCashMoney(): ?Money
    {
        return $this->expectedCashMoney;
    }

    /**
     * Sets Expected Cash Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps expected_cash_money
     */
    public function setExpectedCashMoney(?Money $expectedCashMoney): void
    {
        $this->expectedCashMoney = $expectedCashMoney;
    }

    /**
     * Returns Closed Cash Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getClosedCashMoney(): ?Money
    {
        return $this->closedCashMoney;
    }

    /**
     * Sets Closed Cash Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps closed_cash_money
     */
    public function setClosedCashMoney(?Money $closedCashMoney): void
    {
        $this->closedCashMoney = $closedCashMoney;
    }

    /**
     * Returns Device.
     */
    public function getDevice(): ?CashDrawerDevice
    {
        return $this->device;
    }

    /**
     * Sets Device.
     *
     * @maps device
     */
    public function setDevice(?CashDrawerDevice $device): void
    {
        $this->device = $device;
    }

    /**
     * Returns Created At.
     * The shift start time in RFC 3339 format.
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Sets Created At.
     * The shift start time in RFC 3339 format.
     *
     * @maps created_at
     */
    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Returns Updated At.
     * The shift updated at time in RFC 3339 format.
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Sets Updated At.
     * The shift updated at time in RFC 3339 format.
     *
     * @maps updated_at
     */
    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Returns Location Id.
     * The ID of the location the cash drawer shift belongs to.
     */
    public function getLocationId(): ?string
    {
        return $this->locationId;
    }

    /**
     * Sets Location Id.
     * The ID of the location the cash drawer shift belongs to.
     *
     * @maps location_id
     */
    public function setLocationId(?string $locationId): void
    {
        $this->locationId = $locationId;
    }

    /**
     * Returns Team Member Ids.
     * The IDs of all team members that were logged into EDD\Vendor\Square Point of Sale at any
     * point while the cash drawer shift was open.
     *
     * @return string[]|null
     */
    public function getTeamMemberIds(): ?array
    {
        return $this->teamMemberIds;
    }

    /**
     * Sets Team Member Ids.
     * The IDs of all team members that were logged into EDD\Vendor\Square Point of Sale at any
     * point while the cash drawer shift was open.
     *
     * @maps team_member_ids
     *
     * @param string[]|null $teamMemberIds
     */
    public function setTeamMemberIds(?array $teamMemberIds): void
    {
        $this->teamMemberIds = $teamMemberIds;
    }

    /**
     * Returns Opening Team Member Id.
     * The ID of the team member that started the cash drawer shift.
     */
    public function getOpeningTeamMemberId(): ?string
    {
        return $this->openingTeamMemberId;
    }

    /**
     * Sets Opening Team Member Id.
     * The ID of the team member that started the cash drawer shift.
     *
     * @maps opening_team_member_id
     */
    public function setOpeningTeamMemberId(?string $openingTeamMemberId): void
    {
        $this->openingTeamMemberId = $openingTeamMemberId;
    }

    /**
     * Returns Ending Team Member Id.
     * The ID of the team member that ended the cash drawer shift.
     */
    public function getEndingTeamMemberId(): ?string
    {
        return $this->endingTeamMemberId;
    }

    /**
     * Sets Ending Team Member Id.
     * The ID of the team member that ended the cash drawer shift.
     *
     * @maps ending_team_member_id
     */
    public function setEndingTeamMemberId(?string $endingTeamMemberId): void
    {
        $this->endingTeamMemberId = $endingTeamMemberId;
    }

    /**
     * Returns Closing Team Member Id.
     * The ID of the team member that closed the cash drawer shift by auditing
     * the cash drawer contents.
     */
    public function getClosingTeamMemberId(): ?string
    {
        return $this->closingTeamMemberId;
    }

    /**
     * Sets Closing Team Member Id.
     * The ID of the team member that closed the cash drawer shift by auditing
     * the cash drawer contents.
     *
     * @maps closing_team_member_id
     */
    public function setClosingTeamMemberId(?string $closingTeamMemberId): void
    {
        $this->closingTeamMemberId = $closingTeamMemberId;
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
            $json['id']                     = $this->id;
        }
        if (isset($this->state)) {
            $json['state']                  = $this->state;
        }
        if (!empty($this->openedAt)) {
            $json['opened_at']              = $this->openedAt['value'];
        }
        if (!empty($this->endedAt)) {
            $json['ended_at']               = $this->endedAt['value'];
        }
        if (!empty($this->closedAt)) {
            $json['closed_at']              = $this->closedAt['value'];
        }
        if (!empty($this->description)) {
            $json['description']            = $this->description['value'];
        }
        if (isset($this->openedCashMoney)) {
            $json['opened_cash_money']      = $this->openedCashMoney;
        }
        if (isset($this->cashPaymentMoney)) {
            $json['cash_payment_money']     = $this->cashPaymentMoney;
        }
        if (isset($this->cashRefundsMoney)) {
            $json['cash_refunds_money']     = $this->cashRefundsMoney;
        }
        if (isset($this->cashPaidInMoney)) {
            $json['cash_paid_in_money']     = $this->cashPaidInMoney;
        }
        if (isset($this->cashPaidOutMoney)) {
            $json['cash_paid_out_money']    = $this->cashPaidOutMoney;
        }
        if (isset($this->expectedCashMoney)) {
            $json['expected_cash_money']    = $this->expectedCashMoney;
        }
        if (isset($this->closedCashMoney)) {
            $json['closed_cash_money']      = $this->closedCashMoney;
        }
        if (isset($this->device)) {
            $json['device']                 = $this->device;
        }
        if (isset($this->createdAt)) {
            $json['created_at']             = $this->createdAt;
        }
        if (isset($this->updatedAt)) {
            $json['updated_at']             = $this->updatedAt;
        }
        if (isset($this->locationId)) {
            $json['location_id']            = $this->locationId;
        }
        if (isset($this->teamMemberIds)) {
            $json['team_member_ids']        = $this->teamMemberIds;
        }
        if (isset($this->openingTeamMemberId)) {
            $json['opening_team_member_id'] = $this->openingTeamMemberId;
        }
        if (isset($this->endingTeamMemberId)) {
            $json['ending_team_member_id']  = $this->endingTeamMemberId;
        }
        if (isset($this->closingTeamMemberId)) {
            $json['closing_team_member_id'] = $this->closingTeamMemberId;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
