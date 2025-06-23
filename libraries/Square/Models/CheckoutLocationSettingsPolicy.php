<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class CheckoutLocationSettingsPolicy implements \JsonSerializable
{
    /**
     * @var array
     */
    private $uid = [];

    /**
     * @var array
     */
    private $title = [];

    /**
     * @var array
     */
    private $description = [];

    /**
     * Returns Uid.
     * A unique ID to identify the policy when making changes. You must set the UID for policy updates, but
     * it’s optional when setting new policies.
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
     * A unique ID to identify the policy when making changes. You must set the UID for policy updates, but
     * it’s optional when setting new policies.
     *
     * @maps uid
     */
    public function setUid(?string $uid): void
    {
        $this->uid['value'] = $uid;
    }

    /**
     * Unsets Uid.
     * A unique ID to identify the policy when making changes. You must set the UID for policy updates, but
     * it’s optional when setting new policies.
     */
    public function unsetUid(): void
    {
        $this->uid = [];
    }

    /**
     * Returns Title.
     * The title of the policy. This is required when setting the description, though you can update it in
     * a different request.
     */
    public function getTitle(): ?string
    {
        if (count($this->title) == 0) {
            return null;
        }
        return $this->title['value'];
    }

    /**
     * Sets Title.
     * The title of the policy. This is required when setting the description, though you can update it in
     * a different request.
     *
     * @maps title
     */
    public function setTitle(?string $title): void
    {
        $this->title['value'] = $title;
    }

    /**
     * Unsets Title.
     * The title of the policy. This is required when setting the description, though you can update it in
     * a different request.
     */
    public function unsetTitle(): void
    {
        $this->title = [];
    }

    /**
     * Returns Description.
     * The description of the policy.
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
     * The description of the policy.
     *
     * @maps description
     */
    public function setDescription(?string $description): void
    {
        $this->description['value'] = $description;
    }

    /**
     * Unsets Description.
     * The description of the policy.
     */
    public function unsetDescription(): void
    {
        $this->description = [];
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
            $json['uid']         = $this->uid['value'];
        }
        if (!empty($this->title)) {
            $json['title']       = $this->title['value'];
        }
        if (!empty($this->description)) {
            $json['description'] = $this->description['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
