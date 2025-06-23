<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Dispute;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\SubmitEvidenceResponse;

/**
 * Builder for model SubmitEvidenceResponse
 *
 * @see SubmitEvidenceResponse
 */
class SubmitEvidenceResponseBuilder
{
    /**
     * @var SubmitEvidenceResponse
     */
    private $instance;

    private function __construct(SubmitEvidenceResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Submit Evidence Response Builder object.
     */
    public static function init(): self
    {
        return new self(new SubmitEvidenceResponse());
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Sets dispute field.
     *
     * @param Dispute|null $value
     */
    public function dispute(?Dispute $value): self
    {
        $this->instance->setDispute($value);
        return $this;
    }

    /**
     * Initializes a new Submit Evidence Response object.
     */
    public function build(): SubmitEvidenceResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
