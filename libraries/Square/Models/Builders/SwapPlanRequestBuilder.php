<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PhaseInput;
use EDD\Vendor\Square\Models\SwapPlanRequest;

/**
 * Builder for model SwapPlanRequest
 *
 * @see SwapPlanRequest
 */
class SwapPlanRequestBuilder
{
    /**
     * @var SwapPlanRequest
     */
    private $instance;

    private function __construct(SwapPlanRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Swap Plan Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SwapPlanRequest());
    }

    /**
     * Sets new plan variation id field.
     *
     * @param string|null $value
     */
    public function newPlanVariationId(?string $value): self
    {
        $this->instance->setNewPlanVariationId($value);
        return $this;
    }

    /**
     * Unsets new plan variation id field.
     */
    public function unsetNewPlanVariationId(): self
    {
        $this->instance->unsetNewPlanVariationId();
        return $this;
    }

    /**
     * Sets phases field.
     *
     * @param PhaseInput[]|null $value
     */
    public function phases(?array $value): self
    {
        $this->instance->setPhases($value);
        return $this;
    }

    /**
     * Unsets phases field.
     */
    public function unsetPhases(): self
    {
        $this->instance->unsetPhases();
        return $this;
    }

    /**
     * Initializes a new Swap Plan Request object.
     */
    public function build(): SwapPlanRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
