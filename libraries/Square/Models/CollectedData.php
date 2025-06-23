<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class CollectedData implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $inputText;

    /**
     * Returns Input Text.
     * The buyer's input text.
     */
    public function getInputText(): ?string
    {
        return $this->inputText;
    }

    /**
     * Sets Input Text.
     * The buyer's input text.
     *
     * @maps input_text
     */
    public function setInputText(?string $inputText): void
    {
        $this->inputText = $inputText;
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
        if (isset($this->inputText)) {
            $json['input_text'] = $this->inputText;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
