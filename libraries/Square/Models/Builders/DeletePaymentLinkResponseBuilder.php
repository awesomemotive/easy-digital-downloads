<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeletePaymentLinkResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeletePaymentLinkResponse
 *
 * @see DeletePaymentLinkResponse
 */
class DeletePaymentLinkResponseBuilder
{
    /**
     * @var DeletePaymentLinkResponse
     */
    private $instance;

    private function __construct(DeletePaymentLinkResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Payment Link Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeletePaymentLinkResponse());
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
     * Sets cancelled order id field.
     *
     * @param string|null $value
     */
    public function cancelledOrderId(?string $value): self
    {
        $this->instance->setCancelledOrderId($value);
        return $this;
    }

    /**
     * Initializes a new Delete Payment Link Response object.
     */
    public function build(): DeletePaymentLinkResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
