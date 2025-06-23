<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class DataCollectionOptions implements \JsonSerializable
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $inputType;

    /**
     * @var CollectedData|null
     */
    private $collectedData;

    /**
     * @param string $title
     * @param string $body
     * @param string $inputType
     */
    public function __construct(string $title, string $body, string $inputType)
    {
        $this->title = $title;
        $this->body = $body;
        $this->inputType = $inputType;
    }

    /**
     * Returns Title.
     * The title text to display in the data collection flow on the Terminal.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets Title.
     * The title text to display in the data collection flow on the Terminal.
     *
     * @required
     * @maps title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Returns Body.
     * The body text to display under the title in the data collection screen flow on the
     * Terminal.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Sets Body.
     * The body text to display under the title in the data collection screen flow on the
     * Terminal.
     *
     * @required
     * @maps body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Returns Input Type.
     * Describes the input type of the data.
     */
    public function getInputType(): string
    {
        return $this->inputType;
    }

    /**
     * Sets Input Type.
     * Describes the input type of the data.
     *
     * @required
     * @maps input_type
     */
    public function setInputType(string $inputType): void
    {
        $this->inputType = $inputType;
    }

    /**
     * Returns Collected Data.
     */
    public function getCollectedData(): ?CollectedData
    {
        return $this->collectedData;
    }

    /**
     * Sets Collected Data.
     *
     * @maps collected_data
     */
    public function setCollectedData(?CollectedData $collectedData): void
    {
        $this->collectedData = $collectedData;
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
        $json['title']              = $this->title;
        $json['body']               = $this->body;
        $json['input_type']         = $this->inputType;
        if (isset($this->collectedData)) {
            $json['collected_data'] = $this->collectedData;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
