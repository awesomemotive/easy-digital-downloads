<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class SelectOptions implements \JsonSerializable
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
     * @var SelectOption[]
     */
    private $options;

    /**
     * @var SelectOption|null
     */
    private $selectedOption;

    /**
     * @param string $title
     * @param string $body
     * @param SelectOption[] $options
     */
    public function __construct(string $title, string $body, array $options)
    {
        $this->title = $title;
        $this->body = $body;
        $this->options = $options;
    }

    /**
     * Returns Title.
     * The title text to display in the select flow on the Terminal.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets Title.
     * The title text to display in the select flow on the Terminal.
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
     * The body text to display in the select flow on the Terminal.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Sets Body.
     * The body text to display in the select flow on the Terminal.
     *
     * @required
     * @maps body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Returns Options.
     * Represents the buttons/options that should be displayed in the select flow on the Terminal.
     *
     * @return SelectOption[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Sets Options.
     * Represents the buttons/options that should be displayed in the select flow on the Terminal.
     *
     * @required
     * @maps options
     *
     * @param SelectOption[] $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Returns Selected Option.
     */
    public function getSelectedOption(): ?SelectOption
    {
        return $this->selectedOption;
    }

    /**
     * Sets Selected Option.
     *
     * @maps selected_option
     */
    public function setSelectedOption(?SelectOption $selectedOption): void
    {
        $this->selectedOption = $selectedOption;
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
        $json['title']               = $this->title;
        $json['body']                = $this->body;
        $json['options']             = $this->options;
        if (isset($this->selectedOption)) {
            $json['selected_option'] = $this->selectedOption;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
