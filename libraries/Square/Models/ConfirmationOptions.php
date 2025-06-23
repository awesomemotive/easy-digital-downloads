<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class ConfirmationOptions implements \JsonSerializable
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
    private $agreeButtonText;

    /**
     * @var array
     */
    private $disagreeButtonText = [];

    /**
     * @var ConfirmationDecision|null
     */
    private $decision;

    /**
     * @param string $title
     * @param string $body
     * @param string $agreeButtonText
     */
    public function __construct(string $title, string $body, string $agreeButtonText)
    {
        $this->title = $title;
        $this->body = $body;
        $this->agreeButtonText = $agreeButtonText;
    }

    /**
     * Returns Title.
     * The title text to display in the confirmation screen flow on the Terminal.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets Title.
     * The title text to display in the confirmation screen flow on the Terminal.
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
     * The agreement details to display in the confirmation flow on the Terminal.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Sets Body.
     * The agreement details to display in the confirmation flow on the Terminal.
     *
     * @required
     * @maps body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Returns Agree Button Text.
     * The button text to display indicating the customer agrees to the displayed terms.
     */
    public function getAgreeButtonText(): string
    {
        return $this->agreeButtonText;
    }

    /**
     * Sets Agree Button Text.
     * The button text to display indicating the customer agrees to the displayed terms.
     *
     * @required
     * @maps agree_button_text
     */
    public function setAgreeButtonText(string $agreeButtonText): void
    {
        $this->agreeButtonText = $agreeButtonText;
    }

    /**
     * Returns Disagree Button Text.
     * The button text to display indicating the customer does not agree to the displayed terms.
     */
    public function getDisagreeButtonText(): ?string
    {
        if (count($this->disagreeButtonText) == 0) {
            return null;
        }
        return $this->disagreeButtonText['value'];
    }

    /**
     * Sets Disagree Button Text.
     * The button text to display indicating the customer does not agree to the displayed terms.
     *
     * @maps disagree_button_text
     */
    public function setDisagreeButtonText(?string $disagreeButtonText): void
    {
        $this->disagreeButtonText['value'] = $disagreeButtonText;
    }

    /**
     * Unsets Disagree Button Text.
     * The button text to display indicating the customer does not agree to the displayed terms.
     */
    public function unsetDisagreeButtonText(): void
    {
        $this->disagreeButtonText = [];
    }

    /**
     * Returns Decision.
     */
    public function getDecision(): ?ConfirmationDecision
    {
        return $this->decision;
    }

    /**
     * Sets Decision.
     *
     * @maps decision
     */
    public function setDecision(?ConfirmationDecision $decision): void
    {
        $this->decision = $decision;
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
        $json['title']                    = $this->title;
        $json['body']                     = $this->body;
        $json['agree_button_text']        = $this->agreeButtonText;
        if (!empty($this->disagreeButtonText)) {
            $json['disagree_button_text'] = $this->disagreeButtonText['value'];
        }
        if (isset($this->decision)) {
            $json['decision']             = $this->decision;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
