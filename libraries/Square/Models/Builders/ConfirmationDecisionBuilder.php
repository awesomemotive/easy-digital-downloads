<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ConfirmationDecision;

/**
 * Builder for model ConfirmationDecision
 *
 * @see ConfirmationDecision
 */
class ConfirmationDecisionBuilder
{
    /**
     * @var ConfirmationDecision
     */
    private $instance;

    private function __construct(ConfirmationDecision $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Confirmation Decision Builder object.
     */
    public static function init(): self
    {
        return new self(new ConfirmationDecision());
    }

    /**
     * Sets has agreed field.
     *
     * @param bool|null $value
     */
    public function hasAgreed(?bool $value): self
    {
        $this->instance->setHasAgreed($value);
        return $this;
    }

    /**
     * Initializes a new Confirmation Decision object.
     */
    public function build(): ConfirmationDecision
    {
        return CoreHelper::clone($this->instance);
    }
}
