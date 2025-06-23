<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\PaymentLink;
use EDD\Vendor\Square\Models\RetrievePaymentLinkResponse;

/**
 * Builder for model RetrievePaymentLinkResponse
 *
 * @see RetrievePaymentLinkResponse
 */
class RetrievePaymentLinkResponseBuilder
{
    /**
     * @var RetrievePaymentLinkResponse
     */
    private $instance;

    private function __construct(RetrievePaymentLinkResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Payment Link Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrievePaymentLinkResponse());
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
     * Initializes a new Retrieve Payment Link Response object.
     */
    public function build(): RetrievePaymentLinkResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
