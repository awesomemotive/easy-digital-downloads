<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\Request\Parameters;

use EDD\Vendor\Core\Types\Sdk\CoreFileWrapper;
use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestArraySerialization;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestSetterInterface;

class FormParam extends EncodedParam
{
    /**
     * Initializes a form parameter with the key and value provided.
     */
    public static function init(string $key, $value): self
    {
        return new self($key, $value);
    }

    /**
     * @var array<string,string>
     */
    private $encodingHeaders = [];
    private function __construct(string $key, $value)
    {
        parent::__construct($key, $value, 'form');
    }

    /**
     * Sets encoding header with the key and value provided.
     */
    public function encodingHeader(string $key, string $value): self
    {
        $this->encodingHeaders[strtolower($key)] = $value;
        return $this;
    }

    /**
     * Sets the parameter format to un-indexed.
     */
    public function unIndexed(): self
    {
        $this->format = RequestArraySerialization::UN_INDEXED;
        return $this;
    }

    /**
     * Sets the parameter format to plain.
     */
    public function plain(): self
    {
        $this->format = RequestArraySerialization::PLAIN;
        return $this;
    }

    private function isMultipart(): bool
    {
        return isset($this->encodingHeaders['content-type']) &&
            $this->encodingHeaders['content-type'] != 'application/x-www-form-urlencoded';
    }

    /**
     * Adds the parameter to the request provided.
     *
     * @param RequestSetterInterface $request The request to add the parameter to.
     */
    public function apply(RequestSetterInterface $request): void
    {
        if (!$this->validated) {
            return;
        }
        if ($this->value instanceof CoreFileWrapper) {
            if (isset($this->encodingHeaders['content-type'])) {
                $this->value = $this->value->createCurlFileInstance($this->encodingHeaders['content-type']);
            } else {
                $this->value = $this->value->createCurlFileInstance();
            }
            $request->addMultipartFormParam($this->key, $this->value);
            return;
        }
        $this->value = CoreHelper::prepareValue($this->value, !$this->isMultipart());
        if ($this->isMultipart()) {
            $request->addMultipartFormParam($this->key, CoreHelper::serialize($this->value));
            return;
        }
        $encodedValue = $this->httpBuildQuery([$this->key => $this->value], $this->format);
        if (empty($encodedValue)) {
            return;
        }
        $request->addEncodedFormParam($this->key, $encodedValue, $this->value);
    }
}
