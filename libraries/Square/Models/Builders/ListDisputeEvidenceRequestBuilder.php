<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListDisputeEvidenceRequest;

/**
 * Builder for model ListDisputeEvidenceRequest
 *
 * @see ListDisputeEvidenceRequest
 */
class ListDisputeEvidenceRequestBuilder
{
    /**
     * @var ListDisputeEvidenceRequest
     */
    private $instance;

    private function __construct(ListDisputeEvidenceRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Dispute Evidence Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListDisputeEvidenceRequest());
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
        return $this;
    }

    /**
     * Unsets cursor field.
     */
    public function unsetCursor(): self
    {
        $this->instance->unsetCursor();
        return $this;
    }

    /**
     * Initializes a new List Dispute Evidence Request object.
     */
    public function build(): ListDisputeEvidenceRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
