<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Card;
use EDD\Vendor\Square\Models\DestinationDetailsCardRefundDetails;

/**
 * Builder for model DestinationDetailsCardRefundDetails
 *
 * @see DestinationDetailsCardRefundDetails
 */
class DestinationDetailsCardRefundDetailsBuilder
{
    /**
     * @var DestinationDetailsCardRefundDetails
     */
    private $instance;

    private function __construct(DestinationDetailsCardRefundDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Destination Details Card Refund Details Builder object.
     */
    public static function init(): self
    {
        return new self(new DestinationDetailsCardRefundDetails());
    }

    /**
     * Sets card field.
     *
     * @param Card|null $value
     */
    public function card(?Card $value): self
    {
        $this->instance->setCard($value);
        return $this;
    }

    /**
     * Sets entry method field.
     *
     * @param string|null $value
     */
    public function entryMethod(?string $value): self
    {
        $this->instance->setEntryMethod($value);
        return $this;
    }

    /**
     * Unsets entry method field.
     */
    public function unsetEntryMethod(): self
    {
        $this->instance->unsetEntryMethod();
        return $this;
    }

    /**
     * Sets auth result code field.
     *
     * @param string|null $value
     */
    public function authResultCode(?string $value): self
    {
        $this->instance->setAuthResultCode($value);
        return $this;
    }

    /**
     * Unsets auth result code field.
     */
    public function unsetAuthResultCode(): self
    {
        $this->instance->unsetAuthResultCode();
        return $this;
    }

    /**
     * Initializes a new Destination Details Card Refund Details object.
     */
    public function build(): DestinationDetailsCardRefundDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
