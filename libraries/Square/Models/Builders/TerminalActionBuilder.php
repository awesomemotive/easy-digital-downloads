<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ConfirmationOptions;
use EDD\Vendor\Square\Models\DataCollectionOptions;
use EDD\Vendor\Square\Models\DeviceMetadata;
use EDD\Vendor\Square\Models\QrCodeOptions;
use EDD\Vendor\Square\Models\ReceiptOptions;
use EDD\Vendor\Square\Models\SaveCardOptions;
use EDD\Vendor\Square\Models\SelectOptions;
use EDD\Vendor\Square\Models\SignatureOptions;
use EDD\Vendor\Square\Models\TerminalAction;

/**
 * Builder for model TerminalAction
 *
 * @see TerminalAction
 */
class TerminalActionBuilder
{
    /**
     * @var TerminalAction
     */
    private $instance;

    private function __construct(TerminalAction $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Terminal Action Builder object.
     */
    public static function init(): self
    {
        return new self(new TerminalAction());
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets device id field.
     *
     * @param string|null $value
     */
    public function deviceId(?string $value): self
    {
        $this->instance->setDeviceId($value);
        return $this;
    }

    /**
     * Unsets device id field.
     */
    public function unsetDeviceId(): self
    {
        $this->instance->unsetDeviceId();
        return $this;
    }

    /**
     * Sets deadline duration field.
     *
     * @param string|null $value
     */
    public function deadlineDuration(?string $value): self
    {
        $this->instance->setDeadlineDuration($value);
        return $this;
    }

    /**
     * Unsets deadline duration field.
     */
    public function unsetDeadlineDuration(): self
    {
        $this->instance->unsetDeadlineDuration();
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Sets cancel reason field.
     *
     * @param string|null $value
     */
    public function cancelReason(?string $value): self
    {
        $this->instance->setCancelReason($value);
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Sets app id field.
     *
     * @param string|null $value
     */
    public function appId(?string $value): self
    {
        $this->instance->setAppId($value);
        return $this;
    }

    /**
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Sets type field.
     *
     * @param string|null $value
     */
    public function type(?string $value): self
    {
        $this->instance->setType($value);
        return $this;
    }

    /**
     * Sets qr code options field.
     *
     * @param QrCodeOptions|null $value
     */
    public function qrCodeOptions(?QrCodeOptions $value): self
    {
        $this->instance->setQrCodeOptions($value);
        return $this;
    }

    /**
     * Sets save card options field.
     *
     * @param SaveCardOptions|null $value
     */
    public function saveCardOptions(?SaveCardOptions $value): self
    {
        $this->instance->setSaveCardOptions($value);
        return $this;
    }

    /**
     * Sets signature options field.
     *
     * @param SignatureOptions|null $value
     */
    public function signatureOptions(?SignatureOptions $value): self
    {
        $this->instance->setSignatureOptions($value);
        return $this;
    }

    /**
     * Sets confirmation options field.
     *
     * @param ConfirmationOptions|null $value
     */
    public function confirmationOptions(?ConfirmationOptions $value): self
    {
        $this->instance->setConfirmationOptions($value);
        return $this;
    }

    /**
     * Sets receipt options field.
     *
     * @param ReceiptOptions|null $value
     */
    public function receiptOptions(?ReceiptOptions $value): self
    {
        $this->instance->setReceiptOptions($value);
        return $this;
    }

    /**
     * Sets data collection options field.
     *
     * @param DataCollectionOptions|null $value
     */
    public function dataCollectionOptions(?DataCollectionOptions $value): self
    {
        $this->instance->setDataCollectionOptions($value);
        return $this;
    }

    /**
     * Sets select options field.
     *
     * @param SelectOptions|null $value
     */
    public function selectOptions(?SelectOptions $value): self
    {
        $this->instance->setSelectOptions($value);
        return $this;
    }

    /**
     * Sets device metadata field.
     *
     * @param DeviceMetadata|null $value
     */
    public function deviceMetadata(?DeviceMetadata $value): self
    {
        $this->instance->setDeviceMetadata($value);
        return $this;
    }

    /**
     * Sets await next action field.
     *
     * @param bool|null $value
     */
    public function awaitNextAction(?bool $value): self
    {
        $this->instance->setAwaitNextAction($value);
        return $this;
    }

    /**
     * Unsets await next action field.
     */
    public function unsetAwaitNextAction(): self
    {
        $this->instance->unsetAwaitNextAction();
        return $this;
    }

    /**
     * Sets await next action duration field.
     *
     * @param string|null $value
     */
    public function awaitNextActionDuration(?string $value): self
    {
        $this->instance->setAwaitNextActionDuration($value);
        return $this;
    }

    /**
     * Unsets await next action duration field.
     */
    public function unsetAwaitNextActionDuration(): self
    {
        $this->instance->unsetAwaitNextActionDuration();
        return $this;
    }

    /**
     * Initializes a new Terminal Action object.
     */
    public function build(): TerminalAction
    {
        return CoreHelper::clone($this->instance);
    }
}
