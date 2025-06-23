<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents an action processed by the EDD\Vendor\Square Terminal.
 */
class TerminalAction implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var array
     */
    private $deviceId = [];

    /**
     * @var array
     */
    private $deadlineDuration = [];

    /**
     * @var string|null
     */
    private $status;

    /**
     * @var string|null
     */
    private $cancelReason;

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
    private $appId;

    /**
     * @var string|null
     */
    private $locationId;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var QrCodeOptions|null
     */
    private $qrCodeOptions;

    /**
     * @var SaveCardOptions|null
     */
    private $saveCardOptions;

    /**
     * @var SignatureOptions|null
     */
    private $signatureOptions;

    /**
     * @var ConfirmationOptions|null
     */
    private $confirmationOptions;

    /**
     * @var ReceiptOptions|null
     */
    private $receiptOptions;

    /**
     * @var DataCollectionOptions|null
     */
    private $dataCollectionOptions;

    /**
     * @var SelectOptions|null
     */
    private $selectOptions;

    /**
     * @var DeviceMetadata|null
     */
    private $deviceMetadata;

    /**
     * @var array
     */
    private $awaitNextAction = [];

    /**
     * @var array
     */
    private $awaitNextActionDuration = [];

    /**
     * Returns Id.
     * A unique ID for this `TerminalAction`.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * A unique ID for this `TerminalAction`.
     *
     * @maps id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns Device Id.
     * The unique Id of the device intended for this `TerminalAction`.
     * The Id can be retrieved from /v2/devices api.
     */
    public function getDeviceId(): ?string
    {
        if (count($this->deviceId) == 0) {
            return null;
        }
        return $this->deviceId['value'];
    }

    /**
     * Sets Device Id.
     * The unique Id of the device intended for this `TerminalAction`.
     * The Id can be retrieved from /v2/devices api.
     *
     * @maps device_id
     */
    public function setDeviceId(?string $deviceId): void
    {
        $this->deviceId['value'] = $deviceId;
    }

    /**
     * Unsets Device Id.
     * The unique Id of the device intended for this `TerminalAction`.
     * The Id can be retrieved from /v2/devices api.
     */
    public function unsetDeviceId(): void
    {
        $this->deviceId = [];
    }

    /**
     * Returns Deadline Duration.
     * The duration as an RFC 3339 duration, after which the action will be automatically canceled.
     * TerminalActions that are `PENDING` will be automatically `CANCELED` and have a cancellation reason
     * of `TIMED_OUT`
     *
     * Default: 5 minutes from creation
     *
     * Maximum: 5 minutes
     */
    public function getDeadlineDuration(): ?string
    {
        if (count($this->deadlineDuration) == 0) {
            return null;
        }
        return $this->deadlineDuration['value'];
    }

    /**
     * Sets Deadline Duration.
     * The duration as an RFC 3339 duration, after which the action will be automatically canceled.
     * TerminalActions that are `PENDING` will be automatically `CANCELED` and have a cancellation reason
     * of `TIMED_OUT`
     *
     * Default: 5 minutes from creation
     *
     * Maximum: 5 minutes
     *
     * @maps deadline_duration
     */
    public function setDeadlineDuration(?string $deadlineDuration): void
    {
        $this->deadlineDuration['value'] = $deadlineDuration;
    }

    /**
     * Unsets Deadline Duration.
     * The duration as an RFC 3339 duration, after which the action will be automatically canceled.
     * TerminalActions that are `PENDING` will be automatically `CANCELED` and have a cancellation reason
     * of `TIMED_OUT`
     *
     * Default: 5 minutes from creation
     *
     * Maximum: 5 minutes
     */
    public function unsetDeadlineDuration(): void
    {
        $this->deadlineDuration = [];
    }

    /**
     * Returns Status.
     * The status of the `TerminalAction`.
     * Options: `PENDING`, `IN_PROGRESS`, `CANCEL_REQUESTED`, `CANCELED`, `COMPLETED`
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Sets Status.
     * The status of the `TerminalAction`.
     * Options: `PENDING`, `IN_PROGRESS`, `CANCEL_REQUESTED`, `CANCELED`, `COMPLETED`
     *
     * @maps status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * Returns Cancel Reason.
     */
    public function getCancelReason(): ?string
    {
        return $this->cancelReason;
    }

    /**
     * Sets Cancel Reason.
     *
     * @maps cancel_reason
     */
    public function setCancelReason(?string $cancelReason): void
    {
        $this->cancelReason = $cancelReason;
    }

    /**
     * Returns Created At.
     * The time when the `TerminalAction` was created as an RFC 3339 timestamp.
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Sets Created At.
     * The time when the `TerminalAction` was created as an RFC 3339 timestamp.
     *
     * @maps created_at
     */
    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Returns Updated At.
     * The time when the `TerminalAction` was last updated as an RFC 3339 timestamp.
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Sets Updated At.
     * The time when the `TerminalAction` was last updated as an RFC 3339 timestamp.
     *
     * @maps updated_at
     */
    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Returns App Id.
     * The ID of the application that created the action.
     */
    public function getAppId(): ?string
    {
        return $this->appId;
    }

    /**
     * Sets App Id.
     * The ID of the application that created the action.
     *
     * @maps app_id
     */
    public function setAppId(?string $appId): void
    {
        $this->appId = $appId;
    }

    /**
     * Returns Location Id.
     * The location id the action is attached to, if a link can be made.
     */
    public function getLocationId(): ?string
    {
        return $this->locationId;
    }

    /**
     * Sets Location Id.
     * The location id the action is attached to, if a link can be made.
     *
     * @maps location_id
     */
    public function setLocationId(?string $locationId): void
    {
        $this->locationId = $locationId;
    }

    /**
     * Returns Type.
     * Describes the type of this unit and indicates which field contains the unit information. This is an
     * ‘open’ enum.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Sets Type.
     * Describes the type of this unit and indicates which field contains the unit information. This is an
     * ‘open’ enum.
     *
     * @maps type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns Qr Code Options.
     * Fields to describe the action that displays QR-Codes.
     */
    public function getQrCodeOptions(): ?QrCodeOptions
    {
        return $this->qrCodeOptions;
    }

    /**
     * Sets Qr Code Options.
     * Fields to describe the action that displays QR-Codes.
     *
     * @maps qr_code_options
     */
    public function setQrCodeOptions(?QrCodeOptions $qrCodeOptions): void
    {
        $this->qrCodeOptions = $qrCodeOptions;
    }

    /**
     * Returns Save Card Options.
     * Describes save-card action fields.
     */
    public function getSaveCardOptions(): ?SaveCardOptions
    {
        return $this->saveCardOptions;
    }

    /**
     * Sets Save Card Options.
     * Describes save-card action fields.
     *
     * @maps save_card_options
     */
    public function setSaveCardOptions(?SaveCardOptions $saveCardOptions): void
    {
        $this->saveCardOptions = $saveCardOptions;
    }

    /**
     * Returns Signature Options.
     */
    public function getSignatureOptions(): ?SignatureOptions
    {
        return $this->signatureOptions;
    }

    /**
     * Sets Signature Options.
     *
     * @maps signature_options
     */
    public function setSignatureOptions(?SignatureOptions $signatureOptions): void
    {
        $this->signatureOptions = $signatureOptions;
    }

    /**
     * Returns Confirmation Options.
     */
    public function getConfirmationOptions(): ?ConfirmationOptions
    {
        return $this->confirmationOptions;
    }

    /**
     * Sets Confirmation Options.
     *
     * @maps confirmation_options
     */
    public function setConfirmationOptions(?ConfirmationOptions $confirmationOptions): void
    {
        $this->confirmationOptions = $confirmationOptions;
    }

    /**
     * Returns Receipt Options.
     * Describes receipt action fields.
     */
    public function getReceiptOptions(): ?ReceiptOptions
    {
        return $this->receiptOptions;
    }

    /**
     * Sets Receipt Options.
     * Describes receipt action fields.
     *
     * @maps receipt_options
     */
    public function setReceiptOptions(?ReceiptOptions $receiptOptions): void
    {
        $this->receiptOptions = $receiptOptions;
    }

    /**
     * Returns Data Collection Options.
     */
    public function getDataCollectionOptions(): ?DataCollectionOptions
    {
        return $this->dataCollectionOptions;
    }

    /**
     * Sets Data Collection Options.
     *
     * @maps data_collection_options
     */
    public function setDataCollectionOptions(?DataCollectionOptions $dataCollectionOptions): void
    {
        $this->dataCollectionOptions = $dataCollectionOptions;
    }

    /**
     * Returns Select Options.
     */
    public function getSelectOptions(): ?SelectOptions
    {
        return $this->selectOptions;
    }

    /**
     * Sets Select Options.
     *
     * @maps select_options
     */
    public function setSelectOptions(?SelectOptions $selectOptions): void
    {
        $this->selectOptions = $selectOptions;
    }

    /**
     * Returns Device Metadata.
     */
    public function getDeviceMetadata(): ?DeviceMetadata
    {
        return $this->deviceMetadata;
    }

    /**
     * Sets Device Metadata.
     *
     * @maps device_metadata
     */
    public function setDeviceMetadata(?DeviceMetadata $deviceMetadata): void
    {
        $this->deviceMetadata = $deviceMetadata;
    }

    /**
     * Returns Await Next Action.
     * Indicates the action will be linked to another action and requires a waiting dialog to be
     * displayed instead of returning to the idle screen on completion of the action.
     *
     * Only supported on SIGNATURE, CONFIRMATION, DATA_COLLECTION, and SELECT types.
     */
    public function getAwaitNextAction(): ?bool
    {
        if (count($this->awaitNextAction) == 0) {
            return null;
        }
        return $this->awaitNextAction['value'];
    }

    /**
     * Sets Await Next Action.
     * Indicates the action will be linked to another action and requires a waiting dialog to be
     * displayed instead of returning to the idle screen on completion of the action.
     *
     * Only supported on SIGNATURE, CONFIRMATION, DATA_COLLECTION, and SELECT types.
     *
     * @maps await_next_action
     */
    public function setAwaitNextAction(?bool $awaitNextAction): void
    {
        $this->awaitNextAction['value'] = $awaitNextAction;
    }

    /**
     * Unsets Await Next Action.
     * Indicates the action will be linked to another action and requires a waiting dialog to be
     * displayed instead of returning to the idle screen on completion of the action.
     *
     * Only supported on SIGNATURE, CONFIRMATION, DATA_COLLECTION, and SELECT types.
     */
    public function unsetAwaitNextAction(): void
    {
        $this->awaitNextAction = [];
    }

    /**
     * Returns Await Next Action Duration.
     * The timeout duration of the waiting dialog as an RFC 3339 duration, after which the
     * waiting dialog will no longer be displayed and the Terminal will return to the idle screen.
     *
     * Default: 5 minutes from when the waiting dialog is displayed
     *
     * Maximum: 5 minutes
     */
    public function getAwaitNextActionDuration(): ?string
    {
        if (count($this->awaitNextActionDuration) == 0) {
            return null;
        }
        return $this->awaitNextActionDuration['value'];
    }

    /**
     * Sets Await Next Action Duration.
     * The timeout duration of the waiting dialog as an RFC 3339 duration, after which the
     * waiting dialog will no longer be displayed and the Terminal will return to the idle screen.
     *
     * Default: 5 minutes from when the waiting dialog is displayed
     *
     * Maximum: 5 minutes
     *
     * @maps await_next_action_duration
     */
    public function setAwaitNextActionDuration(?string $awaitNextActionDuration): void
    {
        $this->awaitNextActionDuration['value'] = $awaitNextActionDuration;
    }

    /**
     * Unsets Await Next Action Duration.
     * The timeout duration of the waiting dialog as an RFC 3339 duration, after which the
     * waiting dialog will no longer be displayed and the Terminal will return to the idle screen.
     *
     * Default: 5 minutes from when the waiting dialog is displayed
     *
     * Maximum: 5 minutes
     */
    public function unsetAwaitNextActionDuration(): void
    {
        $this->awaitNextActionDuration = [];
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
            $json['id']                         = $this->id;
        }
        if (!empty($this->deviceId)) {
            $json['device_id']                  = $this->deviceId['value'];
        }
        if (!empty($this->deadlineDuration)) {
            $json['deadline_duration']          = $this->deadlineDuration['value'];
        }
        if (isset($this->status)) {
            $json['status']                     = $this->status;
        }
        if (isset($this->cancelReason)) {
            $json['cancel_reason']              = $this->cancelReason;
        }
        if (isset($this->createdAt)) {
            $json['created_at']                 = $this->createdAt;
        }
        if (isset($this->updatedAt)) {
            $json['updated_at']                 = $this->updatedAt;
        }
        if (isset($this->appId)) {
            $json['app_id']                     = $this->appId;
        }
        if (isset($this->locationId)) {
            $json['location_id']                = $this->locationId;
        }
        if (isset($this->type)) {
            $json['type']                       = $this->type;
        }
        if (isset($this->qrCodeOptions)) {
            $json['qr_code_options']            = $this->qrCodeOptions;
        }
        if (isset($this->saveCardOptions)) {
            $json['save_card_options']          = $this->saveCardOptions;
        }
        if (isset($this->signatureOptions)) {
            $json['signature_options']          = $this->signatureOptions;
        }
        if (isset($this->confirmationOptions)) {
            $json['confirmation_options']       = $this->confirmationOptions;
        }
        if (isset($this->receiptOptions)) {
            $json['receipt_options']            = $this->receiptOptions;
        }
        if (isset($this->dataCollectionOptions)) {
            $json['data_collection_options']    = $this->dataCollectionOptions;
        }
        if (isset($this->selectOptions)) {
            $json['select_options']             = $this->selectOptions;
        }
        if (isset($this->deviceMetadata)) {
            $json['device_metadata']            = $this->deviceMetadata;
        }
        if (!empty($this->awaitNextAction)) {
            $json['await_next_action']          = $this->awaitNextAction['value'];
        }
        if (!empty($this->awaitNextActionDuration)) {
            $json['await_next_action_duration'] = $this->awaitNextActionDuration['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
