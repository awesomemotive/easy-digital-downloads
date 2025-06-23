<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeprecatedCreateDisputeEvidenceTextRequest;

/**
 * Builder for model DeprecatedCreateDisputeEvidenceTextRequest
 *
 * @see DeprecatedCreateDisputeEvidenceTextRequest
 */
class DeprecatedCreateDisputeEvidenceTextRequestBuilder
{
    /**
     * @var DeprecatedCreateDisputeEvidenceTextRequest
     */
    private $instance;

    private function __construct(DeprecatedCreateDisputeEvidenceTextRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Deprecated Create Dispute Evidence Text Request Builder object.
     *
     * @param string $idempotencyKey
     * @param string $evidenceText
     */
    public static function init(string $idempotencyKey, string $evidenceText): self
    {
        return new self(new DeprecatedCreateDisputeEvidenceTextRequest($idempotencyKey, $evidenceText));
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
     * Initializes a new Deprecated Create Dispute Evidence Text Request object.
     */
    public function build(): DeprecatedCreateDisputeEvidenceTextRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
