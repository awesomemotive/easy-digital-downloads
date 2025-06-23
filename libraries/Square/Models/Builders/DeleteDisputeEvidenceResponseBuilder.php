<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteDisputeEvidenceResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteDisputeEvidenceResponse
 *
 * @see DeleteDisputeEvidenceResponse
 */
class DeleteDisputeEvidenceResponseBuilder
{
    /**
     * @var DeleteDisputeEvidenceResponse
     */
    private $instance;

    private function __construct(DeleteDisputeEvidenceResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Dispute Evidence Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteDisputeEvidenceResponse());
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
     * Initializes a new Delete Dispute Evidence Response object.
     */
    public function build(): DeleteDisputeEvidenceResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
