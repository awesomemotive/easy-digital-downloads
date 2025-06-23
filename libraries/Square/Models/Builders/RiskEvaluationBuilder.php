<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\RiskEvaluation;

/**
 * Builder for model RiskEvaluation
 *
 * @see RiskEvaluation
 */
class RiskEvaluationBuilder
{
    /**
     * @var RiskEvaluation
     */
    private $instance;

    private function __construct(RiskEvaluation $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Risk Evaluation Builder object.
     */
    public static function init(): self
    {
        return new self(new RiskEvaluation());
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets risk level field.
     *
     * @param string|null $value
     */
    public function riskLevel(?string $value): self
    {
        $this->instance->setRiskLevel($value);
        return $this;
    }

    /**
     * Initializes a new Risk Evaluation object.
     */
    public function build(): RiskEvaluation
    {
        return CoreHelper::clone($this->instance);
    }
}
