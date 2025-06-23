<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class SignatureOptions implements \JsonSerializable
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
     * @var SignatureImage[]|null
     */
    private $signature;

    /**
     * @param string $title
     * @param string $body
     */
    public function __construct(string $title, string $body)
    {
        $this->title = $title;
        $this->body = $body;
    }

    /**
     * Returns Title.
     * The title text to display in the signature capture flow on the Terminal.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets Title.
     * The title text to display in the signature capture flow on the Terminal.
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
     * The body text to display in the signature capture flow on the Terminal.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Sets Body.
     * The body text to display in the signature capture flow on the Terminal.
     *
     * @required
     * @maps body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Returns Signature.
     * An image representation of the collected signature.
     *
     * @return SignatureImage[]|null
     */
    public function getSignature(): ?array
    {
        return $this->signature;
    }

    /**
     * Sets Signature.
     * An image representation of the collected signature.
     *
     * @maps signature
     *
     * @param SignatureImage[]|null $signature
     */
    public function setSignature(?array $signature): void
    {
        $this->signature = $signature;
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
        $json['title']         = $this->title;
        $json['body']          = $this->body;
        if (isset($this->signature)) {
            $json['signature'] = $this->signature;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
