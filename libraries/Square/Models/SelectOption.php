<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class SelectOption implements \JsonSerializable
{
    /**
     * @var string
     */
    private $referenceId;

    /**
     * @var string
     */
    private $title;

    /**
     * @param string $referenceId
     * @param string $title
     */
    public function __construct(string $referenceId, string $title)
    {
        $this->referenceId = $referenceId;
        $this->title = $title;
    }

    /**
     * Returns Reference Id.
     * The reference id for the option.
     */
    public function getReferenceId(): string
    {
        return $this->referenceId;
    }

    /**
     * Sets Reference Id.
     * The reference id for the option.
     *
     * @required
     * @maps reference_id
     */
    public function setReferenceId(string $referenceId): void
    {
        $this->referenceId = $referenceId;
    }

    /**
     * Returns Title.
     * The title text that displays in the select option button.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets Title.
     * The title text that displays in the select option button.
     *
     * @required
     * @maps title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
        $json['reference_id'] = $this->referenceId;
        $json['title']        = $this->title;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
