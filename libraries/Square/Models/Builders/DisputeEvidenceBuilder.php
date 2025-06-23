<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DisputeEvidence;
use EDD\Vendor\Square\Models\DisputeEvidenceFile;

/**
 * Builder for model DisputeEvidence
 *
 * @see DisputeEvidence
 */
class DisputeEvidenceBuilder
{
    /**
     * @var DisputeEvidence
     */
    private $instance;

    private function __construct(DisputeEvidence $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Dispute Evidence Builder object.
     */
    public static function init(): self
    {
        return new self(new DisputeEvidence());
    }

    /**
     * Sets evidence id field.
     *
     * @param string|null $value
     */
    public function evidenceId(?string $value): self
    {
        $this->instance->setEvidenceId($value);
        return $this;
    }

    /**
     * Unsets evidence id field.
     */
    public function unsetEvidenceId(): self
    {
        $this->instance->unsetEvidenceId();
        return $this;
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets dispute id field.
     *
     * @param string|null $value
     */
    public function disputeId(?string $value): self
    {
        $this->instance->setDisputeId($value);
        return $this;
    }

    /**
     * Unsets dispute id field.
     */
    public function unsetDisputeId(): self
    {
        $this->instance->unsetDisputeId();
        return $this;
    }

    /**
     * Sets evidence file field.
     *
     * @param DisputeEvidenceFile|null $value
     */
    public function evidenceFile(?DisputeEvidenceFile $value): self
    {
        $this->instance->setEvidenceFile($value);
        return $this;
    }

    /**
     * Sets evidence text field.
     *
     * @param string|null $value
     */
    public function evidenceText(?string $value): self
    {
        $this->instance->setEvidenceText($value);
        return $this;
    }

    /**
     * Unsets evidence text field.
     */
    public function unsetEvidenceText(): self
    {
        $this->instance->unsetEvidenceText();
        return $this;
    }

    /**
     * Sets uploaded at field.
     *
     * @param string|null $value
     */
    public function uploadedAt(?string $value): self
    {
        $this->instance->setUploadedAt($value);
        return $this;
    }

    /**
     * Unsets uploaded at field.
     */
    public function unsetUploadedAt(): self
    {
        $this->instance->unsetUploadedAt();
        return $this;
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
     * Initializes a new Dispute Evidence object.
     */
    public function build(): DisputeEvidence
    {
        return CoreHelper::clone($this->instance);
    }
}
