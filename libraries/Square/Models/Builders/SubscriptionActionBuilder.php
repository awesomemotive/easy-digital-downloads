<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Phase;
use EDD\Vendor\Square\Models\SubscriptionAction;

/**
 * Builder for model SubscriptionAction
 *
 * @see SubscriptionAction
 */
class SubscriptionActionBuilder
{
    /**
     * @var SubscriptionAction
     */
    private $instance;

    private function __construct(SubscriptionAction $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Subscription Action Builder object.
     */
    public static function init(): self
    {
        return new self(new SubscriptionAction());
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
     * Sets type field.
     *
     * @param string|null $value
     */
    public function type(?string $value): self
    {
        $this->instance->setType($value);
        return $this;
    }

    /**
     * Sets effective date field.
     *
     * @param string|null $value
     */
    public function effectiveDate(?string $value): self
    {
        $this->instance->setEffectiveDate($value);
        return $this;
    }

    /**
     * Unsets effective date field.
     */
    public function unsetEffectiveDate(): self
    {
        $this->instance->unsetEffectiveDate();
        return $this;
    }

    /**
     * Sets monthly billing anchor date field.
     *
     * @param int|null $value
     */
    public function monthlyBillingAnchorDate(?int $value): self
    {
        $this->instance->setMonthlyBillingAnchorDate($value);
        return $this;
    }

    /**
     * Unsets monthly billing anchor date field.
     */
    public function unsetMonthlyBillingAnchorDate(): self
    {
        $this->instance->unsetMonthlyBillingAnchorDate();
        return $this;
    }

    /**
     * Sets phases field.
     *
     * @param Phase[]|null $value
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
     * Initializes a new Subscription Action object.
     */
    public function build(): SubscriptionAction
    {
        return CoreHelper::clone($this->instance);
    }
}
