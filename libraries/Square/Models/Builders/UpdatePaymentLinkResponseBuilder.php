<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\PaymentLink;
use EDD\Vendor\Square\Models\UpdatePaymentLinkResponse;

/**
 * Builder for model UpdatePaymentLinkResponse
 *
 * @see UpdatePaymentLinkResponse
 */
class UpdatePaymentLinkResponseBuilder
{
    /**
     * @var UpdatePaymentLinkResponse
     */
    private $instance;

    private function __construct(UpdatePaymentLinkResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Payment Link Response Builder object.
     */
    public static function init(): self
    {
        return new self(new UpdatePaymentLinkResponse());
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
     * Initializes a new Update Payment Link Response object.
     */
    public function build(): UpdatePaymentLinkResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
