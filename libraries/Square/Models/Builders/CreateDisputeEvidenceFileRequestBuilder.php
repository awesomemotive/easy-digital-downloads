<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateDisputeEvidenceFileRequest;

/**
 * Builder for model CreateDisputeEvidenceFileRequest
 *
 * @see CreateDisputeEvidenceFileRequest
 */
class CreateDisputeEvidenceFileRequestBuilder
{
    /**
     * @var CreateDisputeEvidenceFileRequest
     */
    private $instance;

    private function __construct(CreateDisputeEvidenceFileRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Dispute Evidence File Request Builder object.
     *
     * @param string $idempotencyKey
     */
    public static function init(string $idempotencyKey): self
    {
        return new self(new CreateDisputeEvidenceFileRequest($idempotencyKey));
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
     * Initializes a new Create Dispute Evidence File Request object.
     */
    public function build(): CreateDisputeEvidenceFileRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
