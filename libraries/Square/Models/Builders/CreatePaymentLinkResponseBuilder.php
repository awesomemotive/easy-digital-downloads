<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreatePaymentLinkResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\PaymentLink;
use EDD\Vendor\Square\Models\PaymentLinkRelatedResources;

/**
 * Builder for model CreatePaymentLinkResponse
 *
 * @see CreatePaymentLinkResponse
 */
class CreatePaymentLinkResponseBuilder
{
    /**
     * @var CreatePaymentLinkResponse
     */
    private $instance;

    private function __construct(CreatePaymentLinkResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Payment Link Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CreatePaymentLinkResponse());
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
     * Sets payment link field.
     *
     * @param PaymentLink|null $value
     */
    public function paymentLink(?PaymentLink $value): self
    {
        $this->instance->setPaymentLink($value);
        return $this;
    }

    /**
     * Sets related resources field.
     *
     * @param PaymentLinkRelatedResources|null $value
     */
    public function relatedResources(?PaymentLinkRelatedResources $value): self
    {
        $this->instance->setRelatedResources($value);
        return $this;
    }

    /**
     * Initializes a new Create Payment Link Response object.
     */
    public function build(): CreatePaymentLinkResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
