<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ResumeSubscriptionRequest;

/**
 * Builder for model ResumeSubscriptionRequest
 *
 * @see ResumeSubscriptionRequest
 */
class ResumeSubscriptionRequestBuilder
{
    /**
     * @var ResumeSubscriptionRequest
     */
    private $instance;

    private function __construct(ResumeSubscriptionRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Resume Subscription Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ResumeSubscriptionRequest());
    }

    /**
     * Sets resume effective date field.
     *
     * @param string|null $value
     */
    public function resumeEffectiveDate(?string $value): self
    {
        $this->instance->setResumeEffectiveDate($value);
        return $this;
    }

    /**
     * Unsets resume effective date field.
     */
    public function unsetResumeEffectiveDate(): self
    {
        $this->instance->unsetResumeEffectiveDate();
        return $this;
    }

    /**
     * Sets resume change timing field.
     *
     * @param string|null $value
     */
    public function resumeChangeTiming(?string $value): self
    {
        $this->instance->setResumeChangeTiming($value);
        return $this;
    }

    /**
     * Initializes a new Resume Subscription Request object.
     */
    public function build(): ResumeSubscriptionRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
