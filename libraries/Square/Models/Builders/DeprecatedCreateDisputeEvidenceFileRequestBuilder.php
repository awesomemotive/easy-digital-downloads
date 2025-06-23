<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeprecatedCreateDisputeEvidenceFileRequest;

/**
 * Builder for model DeprecatedCreateDisputeEvidenceFileRequest
 *
 * @see DeprecatedCreateDisputeEvidenceFileRequest
 */
class DeprecatedCreateDisputeEvidenceFileRequestBuilder
{
    /**
     * @var DeprecatedCreateDisputeEvidenceFileRequest
     */
    private $instance;

    private function __construct(DeprecatedCreateDisputeEvidenceFileRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Deprecated Create Dispute Evidence File Request Builder object.
     *
     * @param string $idempotencyKey
     */
    public static function init(string $idempotencyKey): self
    {
        return new self(new DeprecatedCreateDisputeEvidenceFileRequest($idempotencyKey));
    }

    /**
     * Sets evidence type field.
     *
     * @param string|null $value
     */
    public function evidenceType(?string $value): self
    {
        $this->instance->setEvidenceType($value);
        return $this;
    }

    /**
     * Sets content type field.
     *
     * @param string|null $value
     */
    public function contentType(?string $value): self
    {
        $this->instance->setContentType($value);
        return $this;
    }

    /**
     * Unsets content type field.
     */
    public function unsetContentType(): self
    {
        $this->instance->unsetContentType();
        return $this;
    }

    /**
     * Initializes a new Deprecated Create Dispute Evidence File Request object.
     */
    public function build(): DeprecatedCreateDisputeEvidenceFileRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
