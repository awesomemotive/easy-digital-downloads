<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeprecatedCreateDisputeEvidenceFileResponse;
use EDD\Vendor\Square\Models\DisputeEvidence;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeprecatedCreateDisputeEvidenceFileResponse
 *
 * @see DeprecatedCreateDisputeEvidenceFileResponse
 */
class DeprecatedCreateDisputeEvidenceFileResponseBuilder
{
    /**
     * @var DeprecatedCreateDisputeEvidenceFileResponse
     */
    private $instance;

    private function __construct(DeprecatedCreateDisputeEvidenceFileResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Deprecated Create Dispute Evidence File Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeprecatedCreateDisputeEvidenceFileResponse());
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
     * Sets evidence field.
     *
     * @param DisputeEvidence|null $value
     */
    public function evidence(?DisputeEvidence $value): self
    {
        $this->instance->setEvidence($value);
        return $this;
    }

    /**
     * Initializes a new Deprecated Create Dispute Evidence File Response object.
     */
    public function build(): DeprecatedCreateDisputeEvidenceFileResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
