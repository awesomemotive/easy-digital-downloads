<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogSubscriptionPlanVariation;
use EDD\Vendor\Square\Models\SubscriptionPhase;

/**
 * Builder for model CatalogSubscriptionPlanVariation
 *
 * @see CatalogSubscriptionPlanVariation
 */
class CatalogSubscriptionPlanVariationBuilder
{
    /**
     * @var CatalogSubscriptionPlanVariation
     */
    private $instance;

    private function __construct(CatalogSubscriptionPlanVariation $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Subscription Plan Variation Builder object.
     *
     * @param string $name
     * @param SubscriptionPhase[] $phases
     */
    public static function init(string $name, array $phases): self
    {
        return new self(new CatalogSubscriptionPlanVariation($name, $phases));
    }

    /**
     * Sets subscription plan id field.
     *
     * @param string|null $value
     */
    public function subscriptionPlanId(?string $value): self
    {
        $this->instance->setSubscriptionPlanId($value);
        return $this;
    }

    /**
     * Unsets subscription plan id field.
     */
    public function unsetSubscriptionPlanId(): self
    {
        $this->instance->unsetSubscriptionPlanId();
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
     * Sets can prorate field.
     *
     * @param bool|null $value
     */
    public function canProrate(?bool $value): self
    {
        $this->instance->setCanProrate($value);
        return $this;
    }

    /**
     * Unsets can prorate field.
     */
    public function unsetCanProrate(): self
    {
        $this->instance->unsetCanProrate();
        return $this;
    }

    /**
     * Sets successor plan variation id field.
     *
     * @param string|null $value
     */
    public function successorPlanVariationId(?string $value): self
    {
        $this->instance->setSuccessorPlanVariationId($value);
        return $this;
    }

    /**
     * Unsets successor plan variation id field.
     */
    public function unsetSuccessorPlanVariationId(): self
    {
        $this->instance->unsetSuccessorPlanVariationId();
        return $this;
    }

    /**
     * Initializes a new Catalog Subscription Plan Variation object.
     */
    public function build(): CatalogSubscriptionPlanVariation
    {
        return CoreHelper::clone($this->instance);
    }
}
