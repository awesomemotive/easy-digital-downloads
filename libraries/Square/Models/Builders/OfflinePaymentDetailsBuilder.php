<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\OfflinePaymentDetails;

/**
 * Builder for model OfflinePaymentDetails
 *
 * @see OfflinePaymentDetails
 */
class OfflinePaymentDetailsBuilder
{
    /**
     * @var OfflinePaymentDetails
     */
    private $instance;

    private function __construct(OfflinePaymentDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Offline Payment Details Builder object.
     */
    public static function init(): self
    {
        return new self(new OfflinePaymentDetails());
    }

    /**
     * Sets client created at field.
     *
     * @param string|null $value
     */
    public function clientCreatedAt(?string $value): self
    {
        $this->instance->setClientCreatedAt($value);
        return $this;
    }

    /**
     * Initializes a new Offline Payment Details object.
     */
    public function build(): OfflinePaymentDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
