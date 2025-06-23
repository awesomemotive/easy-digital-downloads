<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentOptions;

/**
 * Builder for model PaymentOptions
 *
 * @see PaymentOptions
 */
class PaymentOptionsBuilder
{
    /**
     * @var PaymentOptions
     */
    private $instance;

    private function __construct(PaymentOptions $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Options Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentOptions());
    }

    /**
     * Sets autocomplete field.
     *
     * @param bool|null $value
     */
    public function autocomplete(?bool $value): self
    {
        $this->instance->setAutocomplete($value);
        return $this;
    }

    /**
     * Unsets autocomplete field.
     */
    public function unsetAutocomplete(): self
    {
        $this->instance->unsetAutocomplete();
        return $this;
    }

    /**
     * Sets delay duration field.
     *
     * @param string|null $value
     */
    public function delayDuration(?string $value): self
    {
        $this->instance->setDelayDuration($value);
        return $this;
    }

    /**
     * Unsets delay duration field.
     */
    public function unsetDelayDuration(): self
    {
        $this->instance->unsetDelayDuration();
        return $this;
    }

    /**
     * Sets accept partial authorization field.
     *
     * @param bool|null $value
     */
    public function acceptPartialAuthorization(?bool $value): self
    {
        $this->instance->setAcceptPartialAuthorization($value);
        return $this;
    }

    /**
     * Unsets accept partial authorization field.
     */
    public function unsetAcceptPartialAuthorization(): self
    {
        $this->instance->unsetAcceptPartialAuthorization();
        return $this;
    }

    /**
     * Sets delay action field.
     *
     * @param string|null $value
     */
    public function delayAction(?string $value): self
    {
        $this->instance->setDelayAction($value);
        return $this;
    }

    /**
     * Initializes a new Payment Options object.
     */
    public function build(): PaymentOptions
    {
        return CoreHelper::clone($this->instance);
    }
}
