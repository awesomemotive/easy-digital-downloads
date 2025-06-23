<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ExternalPaymentDetails;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model ExternalPaymentDetails
 *
 * @see ExternalPaymentDetails
 */
class ExternalPaymentDetailsBuilder
{
    /**
     * @var ExternalPaymentDetails
     */
    private $instance;

    private function __construct(ExternalPaymentDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new External Payment Details Builder object.
     *
     * @param string $type
     * @param string $source
     */
    public static function init(string $type, string $source): self
    {
        return new self(new ExternalPaymentDetails($type, $source));
    }

    /**
     * Sets source id field.
     *
     * @param string|null $value
     */
    public function sourceId(?string $value): self
    {
        $this->instance->setSourceId($value);
        return $this;
    }

    /**
     * Unsets source id field.
     */
    public function unsetSourceId(): self
    {
        $this->instance->unsetSourceId();
        return $this;
    }

    /**
     * Sets source fee money field.
     *
     * @param Money|null $value
     */
    public function sourceFeeMoney(?Money $value): self
    {
        $this->instance->setSourceFeeMoney($value);
        return $this;
    }

    /**
     * Initializes a new External Payment Details object.
     */
    public function build(): ExternalPaymentDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
