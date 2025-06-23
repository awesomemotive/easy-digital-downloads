<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListPaymentLinksResponse;
use EDD\Vendor\Square\Models\PaymentLink;

/**
 * Builder for model ListPaymentLinksResponse
 *
 * @see ListPaymentLinksResponse
 */
class ListPaymentLinksResponseBuilder
{
    /**
     * @var ListPaymentLinksResponse
     */
    private $instance;

    private function __construct(ListPaymentLinksResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Payment Links Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListPaymentLinksResponse());
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
     * Sets payment links field.
     *
     * @param PaymentLink[]|null $value
     */
    public function paymentLinks(?array $value): self
    {
        $this->instance->setPaymentLinks($value);
        return $this;
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
     * Initializes a new List Payment Links Response object.
     */
    public function build(): ListPaymentLinksResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
